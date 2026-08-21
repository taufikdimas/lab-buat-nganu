<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Document;
use App\Services\MarkdownLiteService;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Document $document)
    {
        $this->authorize('view', $document);

        return $document->comments()->with('user')->paginate();
    }

    public function store(Request $request, Document $document, MarkdownLiteService $renderer)
    {
        $this->authorize('create', [Comment::class, $document]);
        $data = $request->validate(['body' => ['required', 'string', 'max:5000']]);

        return response()->json($document->comments()->create(['user_id' => $request->user()->id, 'body_raw' => $data['body'], 'body_rendered' => $renderer->render($data['body'])]), 201);
    }

    public function update(Request $request, Comment $comment, MarkdownLiteService $renderer)
    {
        $this->authorize('update', $comment);
        $data = $request->validate(['body' => ['required', 'string', 'max:5000']]);
        $comment->update(['body_raw' => $data['body'], 'body_rendered' => $renderer->render($data['body'])]);

        return $comment;
    }

    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);
        $comment->delete();

        return response()->noContent();
    }
}
