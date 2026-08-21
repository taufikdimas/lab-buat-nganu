<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use Livewire\Component;
use Livewire\WithPagination;

class AuditLogTable extends Component
{
    use WithPagination;

    public string $search = '';

    public function render()
    {
        return view('livewire.admin.audit-log-table', ['logs' => AuditLog::with('actor')->when($this->search, fn ($q) => $q->where('action', 'like', "%{$this->search}%"))->latest()->paginate(20)]);
    }
}
