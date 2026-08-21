<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class UserTable extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = 'all';

    public function toggleStatus(int $id): void
    {
        $user = User::findOrFail($id);
        abort_if($user->id === auth()->id(), 422, 'You cannot suspend your own account.');
        $user->update(['status' => $user->status === 'active' ? 'suspended' : 'active']);
        AuditLog::create(['actor_id' => auth()->id(), 'action' => 'user.'.$user->status, 'target_type' => 'User', 'target_id' => $user->id, 'ip_address' => request()->ip(), 'user_agent' => request()->userAgent()]);
    }

    public function toggleRole(int $id): void
    {
        $user = User::findOrFail($id);
        abort_if($user->id === auth()->id(), 422, 'You cannot demote your own account.');
        $user->update(['system_role' => $user->system_role === 'admin' ? 'user' : 'admin']);
    }

    public function render()
    {
        $users = User::query()->when($this->search, fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$this->search}%")->orWhere('email', 'like', "%{$this->search}%")))->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))->latest()->paginate(15);

        return view('livewire.admin.user-table', compact('users'));
    }
}
