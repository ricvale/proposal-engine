@extends('layouts.app')

@section('content')
    <h1>Proposals</h1>
    <p class="sub">Every brief you've turned into a proposal, newest first.</p>

    <p style="margin-bottom: 24px;">
        <a class="btn" href="{{ route('proposals.create') }}">New proposal</a>
    </p>

    <div class="card">
        @if ($proposals->isEmpty())
            <div class="empty">
                No proposals yet. Paste your first client brief and let the engine draft it.
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($proposals as $proposal)
                        <tr>
                            <td><a class="title-link" href="{{ route('proposals.edit', $proposal) }}">{{ $proposal->title }}</a></td>
                            <td>{{ $proposal->project_type ?? '—' }}</td>
                            <td><span @class(['pill', 'final' => $proposal->status === 'final'])>{{ $proposal->status }}</span></td>
                            <td>{{ $proposal->created_at->format('M j, Y') }}</td>
                            <td>
                                <div class="row-actions">
                                    <a class="btn secondary" style="padding: 6px 12px; font-size: 13px;" href="{{ route('proposals.pdf', $proposal) }}">PDF</a>
                                    <form method="POST" action="{{ route('proposals.destroy', $proposal) }}" onsubmit="return confirm('Delete this proposal?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn danger" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
