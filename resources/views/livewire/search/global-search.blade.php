<div>
    <x-page-header title="Search" description="Find projects, documents, and people across WorkHub." />
    <flux:input wire:model.live.debounce.350ms="query" icon="magnifying-glass" size="lg" placeholder="Search everything…" autofocus clearable />
    @if(mb_strlen($query) < 2)<div class="mt-8"><x-empty-state icon="magnifying-glass" title="Start typing to search" description="Enter at least two characters to search your workspace." /></div>@else
        <div class="mt-8 grid gap-6 lg:grid-cols-3">
            @foreach(['projects' => ['Projects','folder'], 'documents' => ['Documents','document-text'], 'users' => ['People','users']] as $group => [$label,$icon])
                <section class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900"><div class="flex items-center gap-2"><flux:icon :name="$icon" class="size-4 text-zinc-400" /><h2 class="font-semibold">{{ $label }}</h2><span class="text-xs text-zinc-400">{{ $results[$group]->count() }}</span></div><div class="mt-4 space-y-2">@forelse($results[$group] as $result)@if($group === 'projects')<a href="{{ route('projects.show', $result->id) }}" wire:navigate.hover class="block rounded-xl p-3 hover:bg-zinc-50 dark:hover:bg-zinc-800"><div class="text-sm font-medium">{{ $result->name }}</div><div class="mt-1 truncate text-xs text-zinc-500">{{ $result->description }}</div></a>@elseif($group === 'documents')<a href="{{ route('documents.show', [$result->project_id, $result->id]) }}" wire:navigate.hover class="block rounded-xl p-3 hover:bg-zinc-50 dark:hover:bg-zinc-800"><div class="text-sm font-medium">{{ $result->name }}</div><div class="mt-1 text-xs text-zinc-500">{{ $result->original_filename }}</div></a>@else<div class="rounded-xl p-3"><div class="text-sm font-medium">{{ $result->name }}</div><div class="mt-1 text-xs text-zinc-500">{{ $result->email }}</div></div>@endif @empty<p class="py-8 text-center text-sm text-zinc-500">No {{ strtolower($label) }} found.</p>@endforelse</div></section>
            @endforeach
        </div>
    @endif
</div>
