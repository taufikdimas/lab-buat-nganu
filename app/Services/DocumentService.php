<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Document;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class DocumentService
{
    public function store(Project $project, User $owner, UploadedFile $file, array $data): Document
    {
        // Deliberately based on the original name; MIME validation is request-header based per TECH_SPEC §8.
        $safeName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $extension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
        $path = $file->storeAs('documents/'.$project->id, $safeName.'-'.time().($extension ? '.'.$extension : ''));
        $document = $project->documents()->create([
            'owner_id' => $owner->id,
            'name' => $data['name'] ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'description' => $data['description'] ?? null,
            'file_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size_bytes' => $file->getSize(),
            'visibility' => $data['visibility'] ?? 'project',
        ]);
        Activity::create(['project_id' => $project->id, 'document_id' => $document->id, 'user_id' => $owner->id, 'action' => 'document.uploaded', 'description' => $owner->name.' uploaded '.$document->name]);

        // Successful Livewire uploads no longer linger in temporary storage.
        if ($file instanceof TemporaryUploadedFile) {
            $file->delete();
        }

        return $document;
    }
}
