<?php

use App\ValueObjects\GeneratedProposal;

it('round-trips a valid payload', function () {
    $data = [
        'summary' => 'A summary.',
        'scope' => ['One', 'Two'],
        'deliverables' => ['App'],
        'timeline' => 'Four weeks.',
        'pricing' => '$3,500.',
        'assumptions' => ['Access provided'],
        'next_steps' => ['Call'],
    ];

    expect(GeneratedProposal::fromArray($data)->toArray())->toBe($data);
});

it('rejects a payload with a missing section', function () {
    GeneratedProposal::fromArray(['summary' => 'Only a summary.']);
})->throws(InvalidArgumentException::class, 'scope');

it('rejects empty list sections', function () {
    GeneratedProposal::fromArray([
        'summary' => 'A summary.',
        'scope' => [],
        'deliverables' => ['App'],
        'timeline' => 'Four weeks.',
        'pricing' => '$3,500.',
        'assumptions' => ['Access provided'],
        'next_steps' => ['Call'],
    ]);
})->throws(InvalidArgumentException::class);

it('trims strings and drops blank list items', function () {
    $generated = GeneratedProposal::fromArray([
        'summary' => '  padded  ',
        'scope' => ['  One  ', '', '  '],
        'deliverables' => ['App'],
        'timeline' => 'Four weeks.',
        'pricing' => '$3,500.',
        'assumptions' => ['Access provided'],
        'next_steps' => ['Call'],
    ]);

    expect($generated->summary)->toBe('padded')
        ->and($generated->scope)->toBe(['One']);
});
