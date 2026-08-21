<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentShare;
use App\Models\DocumentShareLink;
use App\Models\User;
use App\Services\ShareLinkService;
use Illuminate\Http\Request;

class ShareController extends Controller
{
    public function store(Request $request, Document $document)
    {
        $this->authorize('share', $document);
        $data = $request->validate(['email' => ['required', 'email', 'exists:users,email'], 'permission' => ['required', 'in:viewer,editor']]);
        $user = User::where('email', $data['email'])->firstOrFail();

        return response()->json(DocumentShare::updateOrCreate(['document_id' => $document->id, 'user_id' => $user->id], ['permission' => $data['permission'], 'shared_by' => $request->user()->id]), 201);
    }

    public function destroy(Request $request, Document $document)
    {
        $this->authorize('share', $document);
        $request->validate(['user_id' => ['required', 'exists:users,id']]);
        $document->shares()->where('user_id', $request->integer('user_id'))->delete();

        return response()->noContent();
    }

    public function storeLink(Request $request, Document $document, ShareLinkService $service)
    {
        $this->authorize('share', $document);
        $data = $request->validate(['expires_at' => ['nullable', 'date', 'after:now']]);

        return response()->json($service->generate($document, $request->user(), isset($data['expires_at']) ? now()->parse($data['expires_at']) : null), 201);
    }

    public function destroyLink(DocumentShareLink $shareLink)
    {
        $this->authorize('share', $shareLink->document);
        $shareLink->update(['revoked_at' => now()]);

        return response()->noContent();
    }
}
