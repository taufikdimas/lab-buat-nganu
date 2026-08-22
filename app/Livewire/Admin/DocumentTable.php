<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class DocumentTable extends Component
{
    use WithPagination;

    public string $search = '';

    public string $visibility = 'all';

    public function delete(int $id): void
    {
        $document = Document::withTrashed()->findOrFail($id);
        abort_if($document->trashed(), 422, 'This document is already deleted.');
        $document->delete();
        $this->audit('content.deleted', $document->id);
    }

    public function restore(int $id): void
    {
        $document = Document::onlyTrashed()->findOrFail($id);
        $document->restore();
        $this->audit('content.restored', $document->id);
    }

    public function forceDelete(int $id): void
    {
        $document = Document::onlyTrashed()->findOrFail($id);
        $path = $document->file_path;
        $document->forceDelete();
        Storage::delete($path);
        $this->audit('content.force_deleted', $id);
    }

    public function render()
    {
        $documents = Document::withTrashed()
            ->with([
                'owner:id,name',
                'project' => fn ($query) => $query->withTrashed()->select(['id', 'name', 'deleted_at']),
            ])
            ->when($this->search, fn ($query) => $query->where('name', 'like', "%{$this->search}%"))
            ->when($this->visibility !== 'all', fn ($query) => $query->where('visibility', $this->visibility))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.document-table', compact('documents'));
    }

    private function audit(string $action, int $documentId): void
    {
        AuditLog::create(['actor_id' => auth()->id(), 'action' => $action, 'target_type' => 'Document', 'target_id' => $documentId, 'ip_address' => request()->ip(), 'user_agent' => request()->userAgent()]);
    }
}
