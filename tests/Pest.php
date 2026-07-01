<?php

use App\Models\ProfileContext;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every line. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Seed the single local user with a filled profile context (no auth in v1).
 */
function localUser(): User
{
    $user = User::factory()->create();

    ProfileContext::query()->create([
        'user_id' => $user->id,
        'bio' => 'Full-stack Laravel developer with 8 years of experience.',
        'rate_card' => 'Fixed-fee milestones. $75/hour equivalent.',
        'past_projects' => 'Built a booking platform for a dental chain.',
        'default_assumptions' => 'Two rounds of revisions included. 50% deposit.',
    ]);

    return $user;
}

/**
 * A valid generated_content payload matching the proposal schema.
 *
 * @return array<string, string|list<string>>
 */
function fakeGeneratedContent(): array
{
    return [
        'summary' => 'I will build the dashboard you described.',
        'scope' => ['Build the dashboard', 'Integrate Shopify'],
        'deliverables' => ['Working web app', 'Documentation'],
        'timeline' => 'Four weeks, in two phases.',
        'pricing' => '$3,500 fixed fee with a 50% deposit.',
        'assumptions' => ['Client provides API access'],
        'next_steps' => ['Book a discovery call', 'Approve the milestone plan'],
    ];
}
