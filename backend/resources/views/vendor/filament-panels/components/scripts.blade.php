@php
    $umamiScript = config('services.umami.script_url');
    $umamiSite = config('services.umami.website_id');
@endphp

@if ($umamiScript && $umamiSite)
    <script async defer src="{{ $umamiScript }}" data-website-id="{{ $umamiSite }}"></script>
@endif

@filamentScripts
