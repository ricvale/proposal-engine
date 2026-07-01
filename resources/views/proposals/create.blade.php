@extends('layouts.app')

@section('content')
    <h1>New proposal</h1>
    <p class="sub">Paste the client's brief as you received it — the engine writes the first draft, you polish it.</p>

    @if ($profileIsEmpty)
        <div class="flash" style="border-color: var(--danger); color: var(--danger); background: #fbeeec;">
            Your profile is empty — the proposal will read generic.
            <a href="{{ route('profile.edit') }}" style="color: inherit;">Fill in your bio, rates, and past projects first.</a>
        </div>
    @endif

    <form method="POST" action="{{ route('proposals.store') }}" onsubmit="document.getElementById('generating').style.display='block'; this.querySelector('button[type=submit]').disabled=true;">
        @csrf
        <div class="card">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" value="{{ old('title') }}" placeholder="Inventory dashboard for Shopify store" required>
            @error('title') <p class="error">{{ $message }}</p> @enderror

            <label for="client_brief">Client brief <span class="hint">— paste the email / job post verbatim</span></label>
            <textarea id="client_brief" name="client_brief" rows="10" required placeholder="We run a Shopify store doing about 2k orders a month...">{{ old('client_brief') }}</textarea>
            @error('client_brief') <p class="error">{{ $message }}</p> @enderror

            <label for="project_type">Project type <span class="hint">(optional)</span></label>
            <input type="text" id="project_type" name="project_type" value="{{ old('project_type') }}" placeholder="Web app / dashboard">

            <label for="budget_hint">Budget hint <span class="hint">(optional)</span></label>
            <input type="text" id="budget_hint" name="budget_hint" value="{{ old('budget_hint') }}" placeholder="$3-4k">

            <label for="timeline_hint">Timeline hint <span class="hint">(optional)</span></label>
            <input type="text" id="timeline_hint" name="timeline_hint" value="{{ old('timeline_hint') }}" placeholder="Live within a month">

            <label for="tech_stack">Tech stack <span class="hint">(optional)</span></label>
            <input type="text" id="tech_stack" name="tech_stack" value="{{ old('tech_stack') }}" placeholder="Laravel preferred">

            <button class="btn" type="submit">Generate proposal</button>
            <p class="generating" id="generating">Generating… local models can take a couple of minutes. Don't close this tab.</p>
        </div>
    </form>
@endsection
