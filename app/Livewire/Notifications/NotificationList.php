<?php

namespace App\Livewire\Notifications;

use Livewire\Component;

class NotificationList extends Component
{
    public function markRead(int $id): void
    {
        auth()->user()->workNotifications()->findOrFail($id)->update(['read_at' => now()]);
    }

    public function markAllRead(): void
    {
        auth()->user()->workNotifications()->whereNull('read_at')->update(['read_at' => now()]);
    }

    public function acceptInvite(int $notificationId): void
    {
        $notification = auth()->user()->workNotifications()->findOrFail($notificationId);
        $projectId = $notification->data['project_id'] ?? null;
        abort_unless($projectId, 422);
        auth()->user()->memberships()->where('project_id', $projectId)->where('status', 'pending')->update(['status' => 'active', 'joined_at' => now()]);
        $notification->update(['read_at' => now()]);
        session()->flash('success', 'Invitation accepted.');
    }

    public function render()
    {
        return view('livewire.notifications.notification-list', ['notifications' => auth()->user()->workNotifications()->latest()->paginate(20)]);
    }
}
