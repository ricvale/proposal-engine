@extends('layouts.app')

@section('content')
    <h1>Your profile</h1>
    <p class="sub">This is your differentiator — everything here is injected into the prompt so proposals sound like you, not like AI.</p>

    <form method="POST" action="{{ route('profile.update') }}">
        @csrf
        @method('PATCH')
        <div class="card">
            <label for="bio">Bio <span class="hint">— positioning, years of experience</span></label>
            <textarea id="bio" name="bio" rows="4" placeholder="Full-stack Laravel developer with 8 years of experience...">{{ old('bio', $profile->bio) }}</textarea>
            @error('bio') <p class="error">{{ $message }}</p> @enderror

            <label for="rate_card">Rate card <span class="hint">— how you price</span></label>
            <textarea id="rate_card" name="rate_card" rows="4" placeholder="Fixed-fee milestones preferred. Typical rate equivalent: $75/hour...">{{ old('rate_card', $profile->rate_card) }}</textarea>
            @error('rate_card') <p class="error">{{ $message }}</p> @enderror

            <label for="past_projects">Past projects <span class="hint">— 3–5 wins to anchor the AI</span></label>
            <textarea id="past_projects" name="past_projects" rows="6" placeholder="1) Rebuilt a Shopify-to-ERP sync handling 50k orders/month...">{{ old('past_projects', $profile->past_projects) }}</textarea>
            @error('past_projects') <p class="error">{{ $message }}</p> @enderror

            <label for="default_assumptions">Default assumptions <span class="hint">— boilerplate scope protections</span></label>
            <textarea id="default_assumptions" name="default_assumptions" rows="4" placeholder="Two rounds of revisions included. 50% deposit to start...">{{ old('default_assumptions', $profile->default_assumptions) }}</textarea>
            @error('default_assumptions') <p class="error">{{ $message }}</p> @enderror

            <button class="btn" type="submit">Save profile</button>
        </div>
    </form>
@endsection
