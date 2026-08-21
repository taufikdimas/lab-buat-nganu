<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Project;
use App\Models\SystemSetting;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Request $request, Project $project)
    {
        $this->authorize('viewAny', [Document::class, $project]);

        return $project->documents()->where(fn ($q) => $q->where('visibility', 'project')->orWhere('owner_id', $request->user()->id)->orWhereHas('shares', fn ($s) => $s->where('user_id', $request->user()->id)))->with('owner')->paginate();
    }

    public function store(Request $request, Project $project, DocumentService $service)
    {
        $this->authorize('create', [Document::class, $project]);
        $max = (int) SystemSetting::value('max_upload_size_mb', 20) * 1024;
        $data = $request->validate(['file' => ['required', 'file', 'max:'.$max], 'name' => ['nullable', 'string', 'max:255'], 'description' => ['nullable', 'string', 'max:5000'], 'visibility' => ['required', 'in:project,private']]);

        return response()->json($service->store($project, $request->user(), $request->file('file'), $data), 201);
    }

    public function show(Document $document)
    {
        $this->authorize('view', $document);

        return $document->load(['owner', 'project', 'shares.user', 'comments.user']);
    }

    public function update(Request $request, Document $document)
    {
        $this->authorize('update', $document);
        $document->update($request->validate(['name' => ['sometimes', 'string', 'max:255'], 'description' => ['nullable', 'string', 'max:5000'], 'visibility' => ['sometimes', 'in:project,private']]));

        return $document;
    }

    public function destroy(Document $document)
    {
        $this->authorize('delete', $document);
        $document->delete();

        return response()->noContent();
    }

    public function download(Document $document)
    {
        $this->authorize('download', $document);

        return Storage::download($document->file_path, $document->original_filename);
    }
}
