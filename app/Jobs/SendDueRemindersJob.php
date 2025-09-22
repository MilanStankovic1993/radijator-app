<?php

namespace App\Jobs;

use App\Mail\ReminderEmail;
use App\Models\Reminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class SendDueRemindersJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $now = Carbon::now('UTC');

        // 1) PRE-reminder: šalje se X minuta pre "anchor" vremena
        // Anchor = email_at ako postoji, u suprotnom starts_at.
        Reminder::query()
            ->where('pre_email_enabled', true)
            ->whereNull('pre_emailed_at')
            ->whereNotNull('email_to')
            ->chunkById(500, function ($batch) use ($now) {
                /** @var Reminder $r */
                foreach ($batch as $r) {
                    $anchor = $r->email_at?->clone() ?? $r->starts_at?->clone();
                    if (!$anchor) {
                        continue;
                    }
                    $sendAt = $anchor->copy()->subMinutes(max(1, (int)$r->pre_email_offset_minutes));

                    if ($sendAt->lte($now)) {
                        Mail::to($r->email_to)->send(new ReminderEmail($r, isPre: true));
                        $r->forceFill(['pre_emailed_at' => $now])->save();
                    }
                }
            });

        // 2) Glavni mejl: u tačno vreme
        Reminder::query()
            ->whereNull('emailed_at')
            ->whereNotNull('email_to')
            ->whereNotNull('email_at')
            ->where('email_at', '<=', $now)
            ->chunkById(500, function ($batch) use ($now) {
                /** @var Reminder $r */
                foreach ($batch as $r) {
                    Mail::to($r->email_to)->send(new ReminderEmail($r, isPre: false));
                    $r->forceFill(['emailed_at' => $now])->save();
                }
            });
    }
}
