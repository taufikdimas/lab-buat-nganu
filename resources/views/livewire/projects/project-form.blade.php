<div class="mx-auto max-w-2xl">
    <x-page-header title="Create a project" description="Start with a clear name and a short description. You’ll become the project owner." />
    <form wire:submit="save" class="rounded-2xl border border-zinc-200 bg-white p-7 dark:border-zinc-800 dark:bg-zinc-900">
        <div class="space-y-6"><flux:input wire:model="name" label="Project name" placeholder="e.g. Customer portal redesign" autofocus /><flux:textarea wire:model="description" label="Description" placeholder="What is this project trying to achieve?" rows="5" /></div>
        <div class="mt-8 flex justify-end gap-3"><flux:button :href="route('projects.index')" wire:navigate>Cancel</flux:button><flux:button type="submit" variant="primary" wire:loading.attr="disabled">Create project</flux:button></div>
    </form>
</div>
