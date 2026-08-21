@props(['icon' => 'inbox', 'title', 'description' => null])
<div class="grid min-h-64 place-items-center rounded-2xl border border-dashed border-zinc-300 bg-white px-6 text-center dark:border-zinc-700 dark:bg-zinc-900/40">
    <div class="max-w-sm"><div class="mx-auto mb-4 grid size-11 place-items-center rounded-xl bg-zinc-100 text-zinc-500 dark:bg-zinc-800"><flux:icon :name="$icon" class="size-5" /></div><h3 class="font-semibold">{{ $title }}</h3>@if($description)<p class="mt-1 text-sm text-zinc-500">{{ $description }}</p>@endif<div class="mt-5">{{ $slot }}</div></div>
</div>
