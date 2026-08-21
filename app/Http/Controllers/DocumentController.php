<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function download(Document $document)
    {
        $this->authorize('download', $document);
        abort_unless(Storage::exists($document->file_path), 404);

        return Storage::download($document->file_path, $document->original_filename, ['Content-Type' => $document->mime_type]);
    }

    public function preview(Document $document)
    {
        $this->authorize('view', $document);
        abort_unless(Storage::exists($document->file_path), 404);

        return Storage::response($document->file_path, $document->original_filename, ['Content-Type' => $document->mime_type, 'Content-Disposition' => 'inline']);
    }
}
