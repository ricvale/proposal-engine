<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'title',
    'client_brief',
    'project_type',
    'budget_hint',
    'timeline_hint',
    'tech_stack',
    'generated_content',
    'status',
])]
class Proposal extends Model
{
    /**
     * The ordered proposal sections stored in generated_content.
     *
     * @var list<string>
     */
    public const SECTIONS = [
        'summary',
        'scope',
        'deliverables',
        'timeline',
        'pricing',
        'assumptions',
        'next_steps',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'generated_content' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
