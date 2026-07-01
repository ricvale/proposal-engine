<?php

namespace App\Services;

use App\Ai\ProposalWriter;
use App\Models\ProfileContext;
use App\Models\Proposal;
use App\ValueObjects\GeneratedProposal;
use App\ValueObjects\ProposalInput;
use Laravel\Ai\Responses\StructuredAgentResponse;
use RuntimeException;

class ProposalGenerator
{
    /**
     * Generate a proposal from a client brief and persist it.
     */
    public function generate(ProposalInput $input, ProfileContext $profile): Proposal
    {
        $response = (new ProposalWriter($profile))->prompt($input->toPrompt());

        if (! $response instanceof StructuredAgentResponse) {
            throw new RuntimeException('Expected a structured response from the proposal writer agent.');
        }

        $generated = GeneratedProposal::fromArray($response->toArray());

        return Proposal::query()->create([
            'user_id' => $profile->user_id,
            'title' => $input->title,
            'client_brief' => $input->clientBrief,
            'project_type' => $input->projectType,
            'budget_hint' => $input->budgetHint,
            'timeline_hint' => $input->timelineHint,
            'tech_stack' => $input->techStack,
            'generated_content' => $generated->toArray(),
            'status' => 'draft',
        ]);
    }
}
