<div>
    <x-page-header title="Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 18 ? 'afternoon' : 'evening') }}, {{ str(auth()->user()->name)->before(' ') }}" description="Here’s what’s moving across your workspace today.">
        <flux:button variant="primary" icon="plus" :href="route('projects.create')" wire:navigate>New project</flux:button>
    </x-page-header>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($stats as $stat)
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between"><span class="text-sm text-zinc-500">{{ $stat['label'] }}</span><span class="grid size-9 place-items-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-300"><flux:icon :name="$stat['icon']" class="size-4" /></span></div>
                <div class="mt-4 text-3xl font-semibold tracking-tight">{{ $stat['value'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="mt-8 grid gap-6 xl:grid-cols-[1.35fr_.65fr]">
        <section class="rounded-2xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-5 dark:border-zinc-800"><div><h2 class="font-semibold">Recent projects</h2><p class="text-sm text-zinc-500">Your latest active workspaces</p></div><flux:button variant="ghost" size="sm" :href="route('projects.index')" wire:navigate>View all</flux:button></div>
            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse($projects as $project)
                    <a href="{{ route('projects.show', $project) }}" wire:navigate class="flex items-center gap-4 px-6 py-4 transition hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                        <span class="grid size-10 place-items-center rounded-xl bg-indigo-50 text-sm font-semibold text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">{{ str($project->name)->substr(0, 2)->upper() }}</span>
                        <span class="min-w-0 flex-1"><span class="block truncate text-sm font-medium">{{ $project->name }}</span><span class="block truncate text-xs text-zinc-500">Owned by {{ $project->owner->name }}</span></span>
                        <x-status-badge :value="$project->status" /><flux:icon.chevron-right class="size-4 text-zinc-400" />
                    </a>
                @empty<div class="px-6 py-12 text-center text-sm text-zinc-500">No projects yet.</div>@endforelse
            </div>
        </section>
        <section class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
            <h2 class="font-semibold">Recent activity</h2><p class="mt-1 text-sm text-zinc-500">Latest changes in your workspace</p>
            <div class="mt-6 space-y-5">
                @forelse($activities as $activity)
                    <div class="relative flex gap-3 before:absolute before:left-[15px] before:top-8 before:h-[calc(100%+4px)] before:w-px before:bg-zinc-200 last:before:hidden dark:before:bg-zinc-800"><x-avatar :user="$activity->user" size="size-8" /><div class="min-w-0"><p class="text-sm leading-5">{{ $activity->description }}</p><p class="mt-1 text-xs text-zinc-500">{{ $activity->created_at->diffForHumans() }}</p></div></div>
                @empty<p class="text-sm text-zinc-500">No recent activity.</p>@endforelse
            </div>
        </section>
    </div>
</div>
