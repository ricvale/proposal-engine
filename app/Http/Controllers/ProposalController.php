<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProposalRequest;
use App\Models\Proposal;
use App\Models\User;
use App\Services\ProposalGenerator;
use App\ValueObjects\ProposalInput;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProposalController extends Controller
{
    public function index(): View
    {
        return view('proposals.index', [
            'proposals' => User::current()->proposals()->get(),
        ]);
    }

    public function create(): View
    {
        return view('proposals.create', [
            'profileIsEmpty' => blank(User::current()->profileContext()->first()?->bio),
        ]);
    }

    public function store(StoreProposalRequest $request, ProposalGenerator $generator): RedirectResponse
    {
        // Local models can take a couple of minutes to generate.
        set_time_limit(0);

        $profile = User::current()->profileContext()->firstOrFail();

        $proposal = $generator->generate(
            ProposalInput::fromArray($request->validated()),
            $profile,
        );

        return redirect()
            ->route('proposals.edit', $proposal)
            ->with('status', 'Proposal generated — review and edit each section below.');
    }

    public function edit(Proposal $proposal): View
    {
        return view('proposals.edit', ['proposal' => $proposal]);
    }

    public function destroy(Proposal $proposal): RedirectResponse
    {
        $proposal->delete();

        return redirect()->route('proposals.index')->with('status', 'Proposal deleted.');
    }

    public function pdf(Proposal $proposal): Response
    {
        return Pdf::loadView('proposals.pdf', ['proposal' => $proposal])
            ->download(Str::slug($proposal->title).'-proposal.pdf');
    }
}
