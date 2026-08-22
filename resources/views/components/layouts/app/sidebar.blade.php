<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>@include('partials.head')</head>
<body class="min-h-screen bg-zinc-50 text-zinc-950 dark:bg-zinc-950 dark:text-zinc-100">
    <flux:sidebar sticky stashable class="border-r border-zinc-200/80 bg-white dark:border-zinc-800 dark:bg-zinc-950">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-2 py-2" wire:navigate.hover>
            <span class="grid size-9 place-items-center rounded-xl bg-indigo-600 text-sm font-bold text-white shadow-sm">W</span>
            <span><span class="block font-semibold tracking-tight">WorkHub</span><span class="block text-[11px] text-zinc-500">Projects, in sync.</span></span>
        </a>

        @if(auth()->user()->isAdmin())
            <div class="mx-2 mt-3 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 dark:border-rose-900 dark:bg-rose-950/50 dark:text-rose-300">Admin workspace</div>
        @endif

        <flux:navlist variant="outline" class="mt-6">
            <flux:navlist.group heading="Workspace" class="grid">
                <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate.hover>Overview</flux:navlist.item>
                <flux:navlist.item icon="folder" :href="route('projects.index')" :current="request()->routeIs('projects.*')" wire:navigate.hover>Projects</flux:navlist.item>
                <flux:navlist.item icon="magnifying-glass" :href="route('search')" :current="request()->routeIs('search')" wire:navigate.hover>Search</flux:navlist.item>
                <flux:navlist.item icon="bell" :href="route('notifications')" :current="request()->routeIs('notifications')" wire:navigate.hover>
                    Notifications
                    @if(auth()->user()->workNotifications()->whereNull('read_at')->count())
                        <flux:badge size="sm" color="indigo" inset="top bottom">{{ auth()->user()->workNotifications()->whereNull('read_at')->count() }}</flux:badge>
                    @endif
                </flux:navlist.item>
            </flux:navlist.group>
            @if(auth()->user()->isAdmin())
                <flux:navlist.group heading="Administration" class="mt-5 grid">
                    <flux:navlist.item icon="shield-check" :href="route('admin.index')" :current="request()->routeIs('admin.index')" wire:navigate.hover>Admin overview</flux:navlist.item>
                    <flux:navlist.item icon="users" :href="route('admin.users')" :current="request()->routeIs('admin.users')" wire:navigate.hover>Users</flux:navlist.item>
                    <flux:navlist.item icon="folder-open" :href="route('admin.projects')" :current="request()->routeIs('admin.projects')" wire:navigate.hover>All projects</flux:navlist.item>
                    <flux:navlist.item icon="document-text" :href="route('admin.documents')" :current="request()->routeIs('admin.documents')" wire:navigate.hover>All documents</flux:navlist.item>
                    <flux:navlist.item icon="clipboard-document-list" :href="route('admin.audit-logs')" :current="request()->routeIs('admin.audit-logs')" wire:navigate.hover>Audit logs</flux:navlist.item>
                    <flux:navlist.item icon="cog-6-tooth" :href="route('admin.settings')" :current="request()->routeIs('admin.settings')" wire:navigate.hover>Settings</flux:navlist.item>
                </flux:navlist.group>
            @endif
        </flux:navlist>
        <flux:spacer />
        <flux:dropdown position="bottom" align="start">
            <flux:profile :name="auth()->user()->name" :initials="auth()->user()->initials()" icon-trailing="chevrons-up-down" />
            <flux:menu class="w-60">
                <div class="px-2 py-2"><div class="truncate text-sm font-medium">{{ auth()->user()->name }}</div><div class="truncate text-xs text-zinc-500">{{ auth()->user()->email }}</div></div>
                <flux:menu.separator />
                <flux:menu.item :href="route('profile')" icon="user-circle" wire:navigate.hover>Profile</flux:menu.item>
                <flux:menu.item href="/settings/password" icon="key" wire:navigate.hover>Security</flux:menu.item>
                <flux:menu.item href="/settings/appearance" icon="moon" wire:navigate.hover>Appearance</flux:menu.item>
                <flux:menu.separator />
                <form method="POST" action="{{ route('logout') }}">@csrf<flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">Log out</flux:menu.item></form>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    <flux:header class="border-b border-zinc-200 bg-white lg:hidden dark:border-zinc-800 dark:bg-zinc-950">
        <flux:sidebar.toggle icon="bars-2" inset="left" />
        <flux:spacer />
        <span class="text-sm font-semibold">WorkHub</span>
    </flux:header>

    <flux:main class="mx-auto w-full max-w-[1500px] px-6 py-7 lg:px-10 lg:py-9">{{ $slot }}</flux:main>
    <flux:toast />
    @fluxScripts
</body>
</html>
