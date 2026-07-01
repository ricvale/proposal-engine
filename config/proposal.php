<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Proposal Generation Provider & Model
    |--------------------------------------------------------------------------
    |
    | The AI provider and model used to generate proposals. Local development
    | uses Ollama (free, offline); switch to Anthropic for client-quality
    | output. Use exact model IDs — wrong IDs 404 (e.g. claude-sonnet-5).
    |
    */

    'provider' => env('AI_PROVIDER', 'ollama'),
    'model' => env('AI_MODEL', 'qwen3.5:9b'),

];
