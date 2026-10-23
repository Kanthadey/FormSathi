<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<style>
    @page { margin: 48px 56px; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1c2321; line-height: 1.6; }
    .head { margin-bottom: 24px; }
    .name { font-size: 16px; font-weight: bold; }
    .muted { color: #5b6662; }
</style>
</head>
<body>
    <div class="head">
        <div class="name">{{ $cv['contact']['name'] }}</div>
        <div class="muted">{{ $cv['contact']['phone'] }}@if($cv['contact']['email']) · {{ $cv['contact']['email'] }}@endif · {{ $cv['contact']['location'] }}</div>
    </div>
    <p class="muted">{{ now()->format('j F Y') }}</p>
    @if($kit->company)<p>{{ $kit->company }}</p>@endif
    {!! nl2br(e($letter)) !!}
</body>
</html>
