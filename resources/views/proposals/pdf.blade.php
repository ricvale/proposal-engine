<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $proposal->title }}</title>
    <style>
        @page { margin: 60px 70px; }
        body { font-family: Georgia, 'Times New Roman', serif; color: #1a2332; font-size: 13px; line-height: 1.65; }
        .header { border-bottom: 2px solid #0f6b5c; padding-bottom: 14px; margin-bottom: 28px; }
        .header .kicker { font-family: Helvetica, Arial, sans-serif; font-size: 10px; text-transform: uppercase; letter-spacing: 2px; color: #0f6b5c; margin: 0 0 6px; }
        h1 { font-size: 24px; margin: 0; font-weight: 700; }
        .date { font-family: Helvetica, Arial, sans-serif; font-size: 10px; color: #6b7280; margin-top: 6px; }
        h2 { font-family: Helvetica, Arial, sans-serif; font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; color: #0f6b5c; margin: 26px 0 8px; }
        p { margin: 0 0 10px; }
        ul { margin: 0 0 10px; padding-left: 18px; }
        li { margin-bottom: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <p class="kicker">Project proposal</p>
        <h1>{{ $proposal->title }}</h1>
        <p class="date">Prepared {{ $proposal->updated_at->format('F j, Y') }}</p>
    </div>

    @php
        $content = $proposal->generated_content;
        $sections = [
            'summary' => 'Summary',
            'scope' => 'Scope of work',
            'deliverables' => 'Deliverables',
            'timeline' => 'Timeline',
            'pricing' => 'Pricing',
            'assumptions' => 'Assumptions',
            'next_steps' => 'Next steps',
        ];
    @endphp

    @foreach ($sections as $key => $heading)
        @continue(blank($content[$key] ?? null))
        <h2>{{ $heading }}</h2>
        @if (is_array($content[$key]))
            <ul>
                @foreach ($content[$key] as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        @else
            <p>{{ $content[$key] }}</p>
        @endif
    @endforeach
</body>
</html>
