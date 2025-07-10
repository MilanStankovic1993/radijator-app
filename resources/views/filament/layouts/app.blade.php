<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
    window.Laravel = {
        user: @json(auth()->user()),
    };
</script>

@vite('resources/js/app.js')
