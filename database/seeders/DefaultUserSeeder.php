<?php

namespace Database\Seeders;

use App\Models\ProfileContext;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DefaultUserSeeder extends Seeder
{
    /**
     * Seed the single local user and an empty profile context (no auth in v1).
     */
    public function run(): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => 'me@local.test'],
            ['name' => 'Me', 'password' => Str::random(32)],
        );

        ProfileContext::query()->firstOrCreate(['user_id' => $user->id]);
    }
}
