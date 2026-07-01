<?php

namespace App\ValueObjects;

class ProposalInput
{
    public function __construct(
        public readonly string $title,
        public readonly string $clientBrief,
        public readonly ?string $projectType = null,
        public readonly ?string $budgetHint = null,
        public readonly ?string $timelineHint = null,
        public readonly ?string $techStack = null,
    ) {}

    /**
     * @param  array{title: string, client_brief: string, project_type?: ?string, budget_hint?: ?string, timeline_hint?: ?string, tech_stack?: ?string}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'],
            clientBrief: $data['client_brief'],
            projectType: $data['project_type'] ?? null,
            budgetHint: $data['budget_hint'] ?? null,
            timelineHint: $data['timeline_hint'] ?? null,
            techStack: $data['tech_stack'] ?? null,
        );
    }

    /**
     * Build the user prompt sent to the model.
     */
    public function toPrompt(): string
    {
        $hints = collect([
            'Project type' => $this->projectType,
            'Budget hint' => $this->budgetHint,
            'Timeline hint' => $this->timelineHint,
            'Tech stack' => $this->techStack,
        ])->filter()->map(fn ($value, $label) => "- {$label}: {$value}")->implode("\n");

        return "Write a proposal for the following opportunity.\n\n"
            ."Proposal title: {$this->title}\n\n"
            ."Client brief (verbatim, as received):\n\"\"\"\n{$this->clientBrief}\n\"\"\""
            .($hints !== '' ? "\n\nAdditional context:\n{$hints}" : '');
    }
}
