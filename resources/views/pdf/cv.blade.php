<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<style>
    @page { margin: 32px 40px; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 10.5px; color: #1c2321; line-height: 1.45; }
    h1 { font-size: 22px; margin: 0 0 2px; }
    .contact { color: #5b6662; margin-bottom: 14px; }
    h2 { font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: #0f6e62; border-bottom: 1px solid #dfe3e0; padding-bottom: 3px; margin: 16px 0 8px; }
    .item { margin-bottom: 9px; }
    .row { width: 100%; }
    .row td { padding: 0; vertical-align: top; }
    .right { text-align: right; color: #5b6662; white-space: nowrap; }
    .title { font-weight: bold; }
    ul { margin: 3px 0 0; padding-left: 14px; }
    li { margin-bottom: 2px; }
    .skills span { display: inline-block; border: 1px solid #dfe3e0; border-radius: 3px; padding: 1px 6px; margin: 0 4px 4px 0; }
</style>
</head>
<body>
    <h1>{{ $cv['contact']['name'] ?: 'Your Name' }}</h1>
    <div class="contact">
        {{ $cv['contact']['phone'] }}@if($cv['contact']['email']) · {{ $cv['contact']['email'] }}@endif · {{ $cv['contact']['location'] }}
    </div>

    @if($cv['summary'])
        <h2>Profile</h2>
        <p>{{ $cv['summary'] }}</p>
    @endif

    @if(count($cv['skills']))
        <h2>Key skills</h2>
        <div class="skills">@foreach($cv['skills'] as $skill)<span>{{ $skill }}</span>@endforeach</div>
    @endif

    @if(count($cv['work']))
        <h2>Experience</h2>
        @foreach($cv['work'] as $job)
            <div class="item">
                <table class="row"><tr>
                    <td><span class="title">{{ $job['title'] }}</span>, {{ $job['employer'] }}@isset($job['location']), {{ $job['location'] }}@endisset</td>
                    <td class="right">{{ $job['period'] }}</td>
                </tr></table>
                @if(count($job['bullets']))
                    <ul>@foreach($job['bullets'] as $bullet)<li>{{ $bullet }}</li>@endforeach</ul>
                @endif
            </div>
        @endforeach
    @endif

    @if(count($cv['education']))
        <h2>Education</h2>
        @foreach($cv['education'] as $edu)
            <div class="item">
                <table class="row"><tr>
                    <td><span class="title">{{ $edu['degree'] }}</span>@isset($edu['subject']) ({{ $edu['subject'] }})@endisset, {{ $edu['institution'] }}@isset($edu['result']) · {{ $edu['result'] }}@endisset</td>
                    <td class="right">{{ $edu['year'] ?? '' }}</td>
                </tr></table>
            </div>
        @endforeach
    @endif

    @if(count($cv['training']))
        <h2>Training</h2>
        @foreach($cv['training'] as $t)
            <div class="item">{{ $t['name'] }}@isset($t['provider']), {{ $t['provider'] }}@endisset @isset($t['year'])({{ $t['year'] }})@endisset</div>
        @endforeach
    @endif

    @if(count($cv['languages']))
        <h2>Languages</h2>
        <p>{{ collect($cv['languages'])->implode(' · ') }}</p>
    @endif
</body>
</html>
