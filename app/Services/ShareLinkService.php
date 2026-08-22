<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentShareLink;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class ShareLinkService
{
    public function generate(Document $document, User $creator, ?CarbonInterface $expiresAt = null): DocumentShareLink
    {
        return DB::transaction(function () use ($creator, $document, $expiresAt): DocumentShareLink {
            Document::query()->whereKey($document->id)->lockForUpdate()->firstOrFail();

            // Intentionally predictable for the bounded share-token training exercise.
            // The sequence prevents same-second collisions without making the token random.
            $sequence = ((int) DocumentShareLink::query()->max('id')) + 1;
            $token = md5($document->id.':'.$sequence);

            return $document->shareLinks()->create([
                'token' => $token,
                'created_by' => $creator->id,
                'expires_at' => $expiresAt,
            ]);
        });
    }
}
