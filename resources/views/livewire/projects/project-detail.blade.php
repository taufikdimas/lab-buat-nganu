<div>
    @if(session('success'))<div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">{{ session('error') }}</div>@endif
    <x-page-header :title="$project->name" :description="$project->description">
        <x-status-badge :value="$project->status" />
        @if($canArchive)<flux:button wire:click="archive" wire:loading.attr="disabled" wire:target="archive" variant="ghost" icon="archive-box">{{ $project->status === 'archived' ? 'Restore' : 'Archive' }}</flux:button>@endif
    </x-page-header>

    <div class="mb-6 flex gap-1 overflow-x-auto border-b border-zinc-200 dark:border-zinc-800">
        @foreach(['documents' => 'Documents', 'members' => 'Members', 'activity' => 'Activity'] as $key => $label)<button wire:click="$set('tab', '{{ $key }}')" class="border-b-2 px-4 py-3 text-sm font-medium {{ $tab === $key ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-zinc-500 hover:text-zinc-900 dark:hover:text-white' }}">{{ $label }}</button>@endforeach
        @if($canUpdate)<button wire:click="$set('tab', 'settings')" class="border-b-2 px-4 py-3 text-sm font-medium {{ $tab === 'settings' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-zinc-500' }}">Settings</button>@endif
    </div>

    @if($tab === 'documents')
        <div class="grid gap-6 xl:grid-cols-[1fr_340px]">
            <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="border-b border-zinc-200 px-6 py-5 dark:border-zinc-800"><h2 class="font-semibold">Documents</h2><p class="text-sm text-zinc-500">Files visible to you in this project</p></div>
                @if($documents->count())<div class="divide-y divide-zinc-100 dark:divide-zinc-800">@foreach($documents as $document)<a href="{{ route('documents.show', [$project, $document]) }}" wire:navigate.hover wire:key="document-{{ $document->id }}" class="flex items-center gap-4 px-6 py-4 hover:bg-zinc-50 dark:hover:bg-zinc-800/50"><span class="grid size-10 place-items-center rounded-lg bg-zinc-100 text-zinc-500 dark:bg-zinc-800"><flux:icon.document-text class="size-5" /></span><span class="min-w-0 flex-1"><span class="block truncate text-sm font-medium">{{ $document->name }}</span><span class="block text-xs text-zinc-500">{{ $document->owner->name }} · {{ $document->human_size }} · {{ $document->updated_at->diffForHumans() }}</span></span><x-status-badge :value="$document->visibility" /><flux:icon.chevron-right class="size-4 text-zinc-400" /></a>@endforeach</div>@else<div class="p-6"><x-empty-state icon="document-plus" title="No documents yet" description="Upload the first document for this project." /></div>@endif
            </section>
            @if($canUpload)
                @if($project->status === 'active')
                    <section
                        x-data="{ uploading: false, progress: 0 }"
                        x-on:livewire-upload-start="uploading = true; progress = 0"
                        x-on:livewire-upload-progress="progress = $event.detail.progress"
                        x-on:livewire-upload-error="uploading = false"
                        x-on:livewire-upload-finish="uploading = false"
                        class="h-fit rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900"
                    >
                        <h2 class="font-semibold">Upload document</h2><p class="mt-1 text-sm text-zinc-500">Set the details, then choose a file. It will be saved automatically.</p>
                        <div class="mt-5 space-y-4"><flux:input wire:model="documentName" label="Display name" placeholder="Optional" /><flux:select wire:model="visibility" label="Visibility"><flux:select.option value="project">Everyone in project</flux:select.option><flux:select.option value="private">Private</flux:select.option></flux:select><flux:input wire:model="file" type="file" label="File" /></div>
                        <div x-show="uploading" style="display: none;" class="mt-5" aria-live="polite">
                            <div class="mb-2 flex items-center justify-between text-xs text-zinc-500"><span x-text="progress < 100 ? 'Uploading file…' : 'Saving document…'"></span><span x-text="progress + '%'">0%</span></div>
                            <div class="h-2 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800"><div class="h-full rounded-full bg-indigo-600 transition-all duration-150" x-bind:style="`width: ${progress}%`"></div></div>
                        </div>
                        @error('file')<p class="mt-3 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </section>
                @else<div class="h-fit rounded-2xl border border-zinc-200 bg-zinc-100 p-5 text-sm text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900">This project is archived and read-only.</div>@endif
            @endif
        </div>
    @elseif($tab === 'members')
        <div class="grid gap-6 xl:grid-cols-[1fr_340px]">
            <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900"><div class="border-b border-zinc-200 px-6 py-5 dark:border-zinc-800"><h2 class="font-semibold">Project members</h2></div><div class="divide-y divide-zinc-100 dark:divide-zinc-800">@foreach($memberships as $member)<div wire:key="member-{{ $member->id }}" class="flex items-center gap-4 px-6 py-4"><x-avatar :user="$member->user" /><span class="min-w-0 flex-1"><span class="block truncate text-sm font-medium">{{ $member->user->name }}</span><span class="block truncate text-xs text-zinc-500">{{ $member->user->email }}</span></span><x-status-badge :value="$member->role" /><x-status-badge :value="$member->status" />@if($canManageMembers && $member->role !== 'owner')<flux:dropdown><flux:button size="sm" variant="ghost" icon="ellipsis-horizontal" square aria-label="Member actions" /><flux:menu><flux:menu.item wire:click="changeRole({{ $member->id }}, 'editor')">Make editor</flux:menu.item><flux:menu.item wire:click="changeRole({{ $member->id }}, 'viewer')">Make viewer</flux:menu.item>@if($member->status === 'active')<flux:menu.item wire:click="transferOwnership({{ $member->id }})">Transfer ownership</flux:menu.item>@endif<flux:menu.separator /><flux:menu.item wire:click="removeMember({{ $member->id }})" variant="danger">Remove</flux:menu.item></flux:menu></flux:dropdown>@endif</div>@endforeach</div></section>
            @if($canManageMembers)<form wire:submit="invite" class="h-fit rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900"><h2 class="font-semibold">Invite member</h2><div class="mt-5 space-y-4"><flux:input wire:model="inviteEmail" label="Email address" type="email" /><flux:select wire:model="inviteRole" label="Project role"><flux:select.option value="viewer">Viewer</flux:select.option><flux:select.option value="editor">Editor</flux:select.option></flux:select></div><flux:button type="submit" variant="primary" class="mt-5 w-full" wire:loading.attr="disabled" wire:target="invite">Send invitation</flux:button></form>@endif
        </div>
    @elseif($tab === 'activity')
        <section class="max-w-3xl rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900"><div class="space-y-6">@foreach($activities as $activity)<div class="flex gap-4"><x-avatar :user="$activity->user" /><div><p class="text-sm">{{ $activity->description }}</p><p class="mt-1 text-xs text-zinc-500">{{ $activity->created_at->format('M j, Y · H:i') }}</p></div></div>@endforeach</div></section>
    @else
        <div class="max-w-2xl space-y-6"><form wire:submit="saveSettings" class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900"><h2 class="font-semibold">Project settings</h2><div class="mt-5 space-y-5"><flux:input wire:model="editName" label="Project name" /><flux:textarea wire:model="editDescription" label="Description" rows="5" /></div><flux:button type="submit" variant="primary" class="mt-6">Save changes</flux:button></form><section class="rounded-2xl border border-red-200 bg-white p-6 dark:border-red-900 dark:bg-zinc-900"><h2 class="font-semibold text-red-600">Danger zone</h2><p class="mt-1 text-sm text-zinc-500">Deleting a project removes it from normal workspace views.</p><flux:button wire:click="deleteProject" wire:confirm="Delete this project?" variant="danger" class="mt-5">Delete project</flux:button></section></div>
    @endif
</div>
