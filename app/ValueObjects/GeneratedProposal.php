<?php

namespace App\ValueObjects;

use InvalidArgumentException;

class GeneratedProposal
{
    /**
     * @param  list<string>  $scope
     * @param  list<string>  $deliverables
     * @param  list<string>  $assumptions
     * @param  list<string>  $nextSteps
     */
    public function __construct(
        public readonly string $summary,
        public readonly array $scope,
        public readonly array $deliverables,
        public readonly string $timeline,
        public readonly string $pricing,
        public readonly array $assumptions,
        public readonly array $nextSteps,
    ) {}

    /**
     * Build from a structured model response, validating the expected shape.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        foreach (['summary', 'scope', 'deliverables', 'timeline', 'pricing', 'assumptions', 'next_steps'] as $key) {
            if (! array_key_exists($key, $data)) {
                throw new InvalidArgumentException("Generated proposal is missing the [{$key}] section.");
            }
        }

        return new self(
            summary: self::string($data['summary']),
            scope: self::stringList($data['scope']),
            deliverables: self::stringList($data['deliverables']),
            timeline: self::string($data['timeline']),
            pricing: self::string($data['pricing']),
            assumptions: self::stringList($data['assumptions']),
            nextSteps: self::stringList($data['next_steps']),
        );
    }

    /**
     * The section-keyed shape persisted to proposals.generated_content.
     *
     * @return array<string, string|list<string>>
     */
    public function toArray(): array
    {
        return [
            'summary' => $this->summary,
            'scope' => $this->scope,
            'deliverables' => $this->deliverables,
            'timeline' => $this->timeline,
            'pricing' => $this->pricing,
            'assumptions' => $this->assumptions,
            'next_steps' => $this->nextSteps,
        ];
    }

    private static function string(mixed $value): string
    {
        if (! is_string($value) || trim($value) === '') {
            throw new InvalidArgumentException('Expected a non-empty string section in the generated proposal.');
        }

        return trim($value);
    }

    /**
     * @return list<string>
     */
    private static function stringList(mixed $value): array
    {
        if (! is_array($value)) {
            throw new InvalidArgumentException('Expected a list section in the generated proposal.');
        }

        $items = array_values(array_filter(
            array_map(fn ($item) => is_string($item) ? trim($item) : null, $value),
            fn ($item) => $item !== null && $item !== '',
        ));

        if ($items === []) {
            throw new InvalidArgumentException('Expected a non-empty list section in the generated proposal.');
        }

        return $items;
    }
}
