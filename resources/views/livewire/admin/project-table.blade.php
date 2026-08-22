<div>
    <x-page-header title="All projects" description="Review active, archived, and deleted projects." />

    <div class="mb-5 flex gap-3">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search projects…" class="max-w-sm" />
        <flux:select wire:model.live="status" class="max-w-40">
            <flux:select.option value="all">All statuses</flux:select.option>
            <flux:select.option value="active">Active</flux:select.option>
            <flux:select.option value="archived">Archived</flux:select.option>
        </flux:select>
    </div>

    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-50 text-xs uppercase text-zinc-500 dark:bg-zinc-800">
                    <tr>
                        <th class="px-6 py-4">Project</th>
                        <th class="px-6 py-4">Owner</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Members</th>
                        <th class="px-6 py-4">Documents</th>
                        <th class="px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach($projects as $project)
                        <tr wire:key="admin-project-{{ $project->id }}" class="{{ $project->trashed() ? 'opacity-70' : '' }}">
                            <td class="px-6 py-4">
                                @if($project->trashed())
                                    <span class="font-medium">{{ $project->name }}</span>
                                @else
                                    <a class="font-medium hover:text-indigo-600" href="{{ route('projects.show', $project) }}" wire:navigate.hover>{{ $project->name }}</a>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-zinc-500">{{ $project->owner?->name ?? 'Deleted user' }}</td>
                            <td class="px-6 py-4">
                                <div class="flex gap-2">
                                    <x-status-badge :value="$project->status" />
                                    @if($project->trashed())<x-status-badge value="deleted" />@endif
                                </div>
                            </td>
                            <td class="px-6 py-4">{{ $project->memberships_count }}</td>
                            <td class="px-6 py-4">{{ $project->documents_count }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    @if($project->trashed())
                                        <flux:button wire:click="restore({{ $project->id }})" size="sm" variant="ghost">Restore</flux:button>
                                        <flux:button wire:click="forceDelete({{ $project->id }})" wire:confirm="Permanently delete this project, its documents, comments, and shares?" size="sm" variant="danger">Delete forever</flux:button>
                                    @else
                                        <flux:button wire:click="delete({{ $project->id }})" wire:confirm="Delete this project? It can be restored by an admin." size="sm" variant="danger">Delete</flux:button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $projects->links() }}</div>
</div>
