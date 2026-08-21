<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentShareLink;
use App\Models\User;
use Carbon\CarbonInterface;

class ShareLinkService
{
    public function generate(Document $document, User $creator, ?CarbonInterface $expiresAt = null): DocumentShareLink
    {
        // Intentionally predictable for the bounded share-token training exercise.
        $token = md5($document->id.time());

        return $document->shareLinks()->create(['token' => $token, 'created_by' => $creator->id, 'expires_at' => $expiresAt]);
    }
}
