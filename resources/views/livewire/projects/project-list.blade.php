<div>
    <x-page-header title="Projects" description="Plan work, share documents, and keep every decision in one place."><flux:button variant="primary" icon="plus" :href="route('projects.create')" wire:navigate.hover>Create project</flux:button></x-page-header>
    <div class="mb-5 flex items-center gap-3"><flux:select wire:model.live="status" class="max-w-44" size="sm"><flux:select.option value="all">All statuses</flux:select.option><flux:select.option value="active">Active</flux:select.option><flux:select.option value="archived">Archived</flux:select.option></flux:select></div>
    @if($projects->count())
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach($projects as $project)
                @php($membership = $project->memberships->firstWhere('user_id', auth()->id()))
                <a href="{{ route('projects.show', $project) }}" wire:navigate.hover class="group rounded-2xl border border-zinc-200 bg-white p-5 transition hover:-translate-y-0.5 hover:border-indigo-300 hover:shadow-lg hover:shadow-indigo-950/5 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-indigo-700">
                    <div class="flex items-start justify-between"><span class="grid size-11 place-items-center rounded-xl bg-indigo-50 font-semibold text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">{{ str($project->name)->substr(0, 2)->upper() }}</span><x-status-badge :value="$project->status" /></div>
                    <h2 class="mt-5 font-semibold tracking-tight group-hover:text-indigo-600">{{ $project->name }}</h2><p class="mt-2 line-clamp-2 min-h-10 text-sm leading-5 text-zinc-500">{{ $project->description ?: 'No description provided.' }}</p>
                    <div class="mt-5 flex items-center justify-between border-t border-zinc-100 pt-4 text-xs text-zinc-500 dark:border-zinc-800"><span>{{ $project->memberships_count }} members</span>@if($membership)<x-status-badge :value="$membership->role" />@endif</div>
                </a>
            @endforeach
        </div>
        <div class="mt-6">{{ $projects->links() }}</div>
    @else
        <x-empty-state icon="folder-plus" title="No projects found" description="Create a project to bring your team and documents together."><flux:button variant="primary" :href="route('projects.create')" wire:navigate.hover>Create project</flux:button></x-empty-state>
    @endif
</div>
