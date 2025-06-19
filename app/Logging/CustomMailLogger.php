<?php

namespace App\Logging;

use Illuminate\Support\Facades\Mail;
use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;

class CustomMailLogger extends AbstractProcessingHandler
{
    protected function write(array $record): void
    {
        Mail::raw($record['message'], function ($message) {
            $message->to('milan.stankovic@radijator.rs')
                    ->subject('CRITICAL ERROR on Staging (Radijator)')
                    ->from('proizvodnja.app@radijator.rs', 'Radijator Inzenjering');
        });
    }

    public function __invoke(array $config)
    {
        return new Logger('mail', [new self()]);
    }
}
