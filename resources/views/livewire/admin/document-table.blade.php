<div>
    <x-page-header title="All documents" description="A global file manager for active and deleted documents." />

    <div class="mb-5 flex gap-3">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search documents…" class="max-w-sm" />
        <flux:select wire:model.live="visibility" class="max-w-40">
            <flux:select.option value="all">All visibility</flux:select.option>
            <flux:select.option value="project">Project</flux:select.option>
            <flux:select.option value="private">Private</flux:select.option>
        </flux:select>
    </div>

    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-50 text-xs uppercase text-zinc-500 dark:bg-zinc-800">
                    <tr>
                        <th class="px-6 py-4">Document</th>
                        <th class="px-6 py-4">Project</th>
                        <th class="px-6 py-4">Owner</th>
                        <th class="px-6 py-4">Visibility</th>
                        <th class="px-6 py-4">Size</th>
                        <th class="px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach($documents as $document)
                        @php($canOpen = ! $document->trashed() && $document->project && ! $document->project->trashed())
                        <tr wire:key="admin-document-{{ $document->id }}" class="{{ $document->trashed() ? 'opacity-70' : '' }}">
                            <td class="px-6 py-4">
                                @if($canOpen)
                                    <a class="font-medium hover:text-indigo-600" href="{{ route('documents.show', [$document->project, $document]) }}" wire:navigate.hover>{{ $document->name }}</a>
                                @else
                                    <span class="font-medium">{{ $document->name }}</span>
                                @endif
                                @if($document->trashed())
                                    <span class="ml-2"><x-status-badge value="deleted" /></span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-zinc-500">
                                {{ $document->project?->name ?? 'Missing project' }}
                                @if($document->project?->trashed())
                                    <span class="ml-2"><x-status-badge value="deleted" /></span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-zinc-500">{{ $document->owner?->name ?? 'Deleted user' }}</td>
                            <td class="px-6 py-4"><x-status-badge :value="$document->visibility" /></td>
                            <td class="px-6 py-4">{{ $document->human_size }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    @if($document->trashed())
                                        <flux:button wire:click="restore({{ $document->id }})" size="sm" variant="ghost">Restore</flux:button>
                                        <flux:button wire:click="forceDelete({{ $document->id }})" wire:confirm="Permanently delete this document and its stored file?" size="sm" variant="danger">Delete forever</flux:button>
                                    @else
                                        <flux:button wire:click="delete({{ $document->id }})" wire:confirm="Delete this document? It can be restored by an admin." size="sm" variant="danger">Delete</flux:button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $documents->links() }}</div>
</div>
