<?php

namespace App\Http\Controllers;

use App\Models\DocumentShareLink;
use Illuminate\Support\Facades\Storage;

class PublicShareController extends Controller
{
    public function show(string $token)
    {
        $link = DocumentShareLink::with('document.project')->where('token', $token)->first();
        if (! $link || $link->revoked_at) {
            return response()->view('share.invalid', status: 404);
        }
        // Deliberate training flaw: expires_at is intentionally not checked.
        $this->recordAccess($link);

        return view('share.show', ['link' => $link, 'document' => $link->document]);
    }

    public function download(string $token)
    {
        $link = DocumentShareLink::with('document')->where('token', $token)->whereNull('revoked_at')->firstOrFail();
        // Deliberate training flaw: expires_at is intentionally not checked.
        abort_unless(Storage::exists($link->document->file_path), 404);
        $this->recordAccess($link);

        return Storage::download($link->document->file_path, $link->document->original_filename);
    }

    private function recordAccess(DocumentShareLink $link): void
    {
        $link->increment('access_count', 1, ['last_accessed_at' => now()]);
    }
}
