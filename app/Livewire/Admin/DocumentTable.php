<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Document;
use Livewire\Component;
use Livewire\WithPagination;

class DocumentTable extends Component
{
    use WithPagination;

    public string $search = '';

    public string $visibility = 'all';

    public function delete(int $id): void
    {
        $document = Document::findOrFail($id);
        $document->delete();
        AuditLog::create(['actor_id' => auth()->id(), 'action' => 'content.deleted', 'target_type' => 'Document', 'target_id' => $document->id, 'ip_address' => request()->ip(), 'user_agent' => request()->userAgent()]);
    }

    public function render()
    {
        $documents = Document::with(['owner', 'project'])->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))->when($this->visibility !== 'all', fn ($q) => $q->where('visibility', $this->visibility))->latest()->paginate(15);

        return view('livewire.admin.document-table', compact('documents'));
    }
}
