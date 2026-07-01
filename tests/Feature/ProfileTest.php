<?php

it('shows the profile edit form', function () {
    localUser();

    $this->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('Full-stack Laravel developer');
});

it('updates the profile context', function () {
    $user = localUser();

    $this->patch(route('profile.update'), [
        'bio' => 'Updated bio.',
        'rate_card' => 'Updated rates.',
        'past_projects' => 'Updated projects.',
        'default_assumptions' => 'Updated assumptions.',
    ])->assertRedirect(route('profile.edit'));

    expect($user->profileContext()->sole()->bio)->toBe('Updated bio.');
});
