<?php

use App\Ai\ProposalWriter;
use App\Models\Proposal;
use Laravel\Ai\Prompts\AgentPrompt;

it('generates and stores a proposal from a client brief', function () {
    localUser();

    ProposalWriter::fake([fakeGeneratedContent()]);

    $response = $this->post(route('proposals.store'), [
        'title' => 'Inventory dashboard',
        'client_brief' => 'We need a live inventory dashboard for our Shopify store.',
        'project_type' => 'Web app',
        'budget_hint' => '$3-4k',
        'timeline_hint' => '1 month',
        'tech_stack' => 'Laravel',
    ]);

    $proposal = Proposal::query()->sole();

    $response->assertRedirect(route('proposals.edit', $proposal));

    expect($proposal->title)->toBe('Inventory dashboard')
        ->and($proposal->status)->toBe('draft')
        ->and($proposal->generated_content)->toBe(fakeGeneratedContent());

    ProposalWriter::assertPrompted(
        fn (AgentPrompt $prompt) => $prompt->contains('live inventory dashboard'),
    );
});

it('injects the profile context into the agent instructions', function () {
    $user = localUser();

    $writer = new ProposalWriter($user->profileContext()->sole());

    expect((string) $writer->instructions())
        ->toContain('Full-stack Laravel developer')
        ->toContain('$75/hour')
        ->toContain('dental chain')
        ->toContain('50% deposit');
});

it('validates the create form', function () {
    localUser();

    ProposalWriter::fake();

    $this->post(route('proposals.store'), ['title' => '', 'client_brief' => ''])
        ->assertSessionHasErrors(['title', 'client_brief']);

    ProposalWriter::assertNeverPrompted();
});
