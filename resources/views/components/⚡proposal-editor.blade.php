<?php

use App\Models\Proposal;
use Livewire\Component;

new class extends Component
{
    public Proposal $proposal;

    public string $title = '';

    public string $status = 'draft';

    /** @var array<string, string> — list sections edited as one item per line */
    public array $sections = [];

    /** @var array<string, string> */
    private const LABELS = [
        'summary' => 'Summary',
        'scope' => 'Scope',
        'deliverables' => 'Deliverables',
        'timeline' => 'Timeline',
        'pricing' => 'Pricing',
        'assumptions' => 'Assumptions',
        'next_steps' => 'Next steps',
    ];

    /** @var list<string> */
    private const LIST_SECTIONS = ['scope', 'deliverables', 'assumptions', 'next_steps'];

    public function mount(Proposal $proposal): void
    {
        $this->proposal = $proposal;
        $this->title = $proposal->title;
        $this->status = $proposal->status;

        foreach ($proposal->generated_content as $key => $value) {
            $this->sections[$key] = is_array($value) ? implode("\n", $value) : $value;
        }
    }

    public function save(): void
    {
        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'sections' => ['required', 'array'],
            'sections.*' => ['required', 'string'],
        ]);

        $content = [];

        foreach (array_keys(self::LABELS) as $key) {
            $raw = trim($this->sections[$key] ?? '');

            $content[$key] = in_array($key, self::LIST_SECTIONS, true)
                ? array_values(array_filter(array_map('trim', explode("\n", $raw)), fn ($line) => $line !== ''))
                : $raw;
        }

        $this->proposal->update([
            'title' => $this->title,
            'status' => $this->status,
            'generated_content' => $content,
        ]);

        $this->dispatch('saved');
    }

    public function toggleStatus(): void
    {
        $this->status = $this->status === 'draft' ? 'final' : 'draft';
        $this->save();
    }

    public function labels(): array
    {
        return self::LABELS;
    }

    public function isListSection(string $key): bool
    {
        return in_array($key, self::LIST_SECTIONS, true);
    }
};
?>

<div>
    <div class="card">
        <label for="title">Title</label>
        <input type="text" id="title" wire:model="title">
        @error('title') <p class="error">{{ $message }}</p> @enderror
    </div>

    @foreach ($this->labels() as $key => $label)
        <div class="card">
            <label for="section-{{ $key }}">
                {{ $label }}
                @if ($this->isListSection($key))
                    <span class="hint">— one item per line</span>
                @endif
            </label>
            <textarea
                id="section-{{ $key }}"
                wire:model="sections.{{ $key }}"
                rows="{{ $this->isListSection($key) ? 6 : 4 }}"
                style="margin-bottom: 0;"
            ></textarea>
            @error('sections.'.$key) <p class="error" style="margin-top: 8px;">{{ $message }}</p> @enderror
        </div>
    @endforeach

    <div class="card" style="display: flex; gap: 12px; align-items: center;">
        <button class="btn" wire:click="save">Save changes</button>
        <button class="btn secondary" wire:click="toggleStatus">
            {{ $status === 'draft' ? 'Mark as final' : 'Back to draft' }}
        </button>
        <a class="btn secondary" href="{{ route('proposals.pdf', $proposal) }}">Download PDF</a>
        <span class="pill {{ $status === 'final' ? 'final' : '' }}">{{ $status }}</span>
        <span x-data="{ shown: false }" x-on:saved.window="shown = true; setTimeout(() => shown = false, 2000)" x-show="shown" style="color: var(--accent); font-size: 14px;" x-cloak>
            Saved ✓
        </span>
    </div>
</div>
