<?php

use App\Models\Proposal;
use Livewire\Livewire;

function makeProposal(): Proposal
{
    return Proposal::query()->create([
        'user_id' => localUser()->id,
        'title' => 'Inventory dashboard',
        'client_brief' => 'We need a dashboard.',
        'generated_content' => fakeGeneratedContent(),
        'status' => 'draft',
    ]);
}

it('renders the editor with the generated sections', function () {
    $proposal = makeProposal();

    $this->get(route('proposals.edit', $proposal))
        ->assertOk()
        ->assertSee('I will build the dashboard you described.');
});

it('saves edited sections, splitting list sections by line', function () {
    $proposal = makeProposal();

    Livewire::test('proposal-editor', ['proposal' => $proposal])
        ->set('title', 'Renamed proposal')
        ->set('sections.summary', 'A sharper summary.')
        ->set('sections.scope', "First item\n\nSecond item\n")
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('saved');

    $proposal->refresh();

    expect($proposal->title)->toBe('Renamed proposal')
        ->and($proposal->generated_content['summary'])->toBe('A sharper summary.')
        ->and($proposal->generated_content['scope'])->toBe(['First item', 'Second item']);
});

it('toggles the proposal status between draft and final', function () {
    $proposal = makeProposal();

    Livewire::test('proposal-editor', ['proposal' => $proposal])
        ->call('toggleStatus')
        ->assertSet('status', 'final');

    expect($proposal->refresh()->status)->toBe('final');
});

it('exports the proposal as a PDF', function () {
    $proposal = makeProposal();

    $this->get(route('proposals.pdf', $proposal))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

it('deletes a proposal', function () {
    $proposal = makeProposal();

    $this->delete(route('proposals.destroy', $proposal))
        ->assertRedirect(route('proposals.index'));

    expect(Proposal::query()->count())->toBe(0);
});
