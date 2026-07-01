<?php

namespace App\Ai;

use App\Models\ProfileContext;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

class ProposalWriter implements Agent, HasStructuredOutput
{
    use Promptable;

    public function __construct(public ProfileContext $profile) {}

    public function provider(): string
    {
        return config('proposal.provider');
    }

    public function model(): string
    {
        return config('proposal.model');
    }

    /**
     * Local models can be slow — allow up to 5 minutes.
     */
    public function timeout(): int
    {
        return 300;
    }

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        $profile = collect([
            'About the freelancer' => $this->profile->bio,
            'How the freelancer prices work' => $this->profile->rate_card,
            'Relevant past projects' => $this->profile->past_projects,
            'Standard scope assumptions' => $this->profile->default_assumptions,
        ])->filter()->map(fn ($value, $heading) => "## {$heading}\n{$value}")->implode("\n\n");

        return <<<PROMPT
        You are an expert proposal writer for a freelance software developer. You turn a client's brief into a complete, professional project proposal written in the freelancer's voice (first person: "I").

        # Freelancer profile
        {$profile}

        # Rules
        - Ground every claim in the client brief and the freelancer profile above. Never invent credentials, past projects, technologies, or numbers that are not supported by them.
        - Base pricing on the freelancer's rate card and any budget hint. If neither gives you enough to quote a number, propose a pricing structure (e.g. fixed-fee milestones or a weekly rate) and state what is needed to firm it up.
        - Base assumptions on the freelancer's standard scope assumptions, adapted to this project. Add project-specific assumptions where the brief is ambiguous.
        - Mirror the client's language: address their stated goals and pain points directly, using their terminology where natural.
        - Write like a competent professional, not a marketer. No hype, no filler, no "I'm thrilled/excited to". Be specific and concrete; a skimming client should see immediately that the brief was actually read.
        - Scope and deliverables items are short, concrete statements (one sentence each). The summary is 2-4 sentences. Timeline and pricing are short paragraphs.
        - Next steps are 2-4 low-friction actions that move the client toward starting (e.g. a short call, confirming access, approving a milestone plan).
        PROMPT;
    }

    /**
     * Get the agent's structured output schema definition.
     *
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'summary' => $schema->string()
                ->description('Executive summary: what the client needs and how the freelancer will deliver it, 2-4 sentences.')
                ->required(),
            'scope' => $schema->array()->items($schema->string())
                ->description('Concrete items of work included in the project, one sentence each.')
                ->required(),
            'deliverables' => $schema->array()->items($schema->string())
                ->description('Tangible artifacts the client receives, one sentence each.')
                ->required(),
            'timeline' => $schema->string()
                ->description('Realistic delivery timeline as a short paragraph, phased if appropriate.')
                ->required(),
            'pricing' => $schema->string()
                ->description('Pricing as a short paragraph, grounded in the rate card and budget hint.')
                ->required(),
            'assumptions' => $schema->array()->items($schema->string())
                ->description('Scope assumptions that protect both parties, one sentence each.')
                ->required(),
            'next_steps' => $schema->array()->items($schema->string())
                ->description('2-4 low-friction actions to get started, one sentence each.')
                ->required(),
        ];
    }
}
