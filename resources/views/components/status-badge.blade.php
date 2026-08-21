@props(['value'])
@php($color = match($value) {'active' => 'emerald', 'pending' => 'amber', 'suspended' => 'red', 'private' => 'violet', 'project' => 'blue', 'owner' => 'indigo', 'editor' => 'blue', 'admin' => 'red', default => 'zinc'})
<flux:badge :color="$color" size="sm">{{ ucfirst($value) }}</flux:badge>
