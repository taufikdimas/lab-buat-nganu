@props(['title', 'description' => null])
<div {{ $attributes->class(['mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between']) }}>
    <div><flux:heading size="xl" level="1">{{ $title }}</flux:heading>@if($description)<flux:subheading class="mt-1 max-w-2xl">{{ $description }}</flux:subheading>@endif</div>
    @if(trim($slot))<div class="flex shrink-0 items-center gap-2">{{ $slot }}</div>@endif
</div>
