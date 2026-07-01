<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('profile.edit', [
            'profile' => User::current()->profileContext()->firstOrFail(),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        User::current()->profileContext()->firstOrFail()->update($request->validated());

        return redirect()->route('profile.edit')->with('status', 'Profile saved.');
    }
}
