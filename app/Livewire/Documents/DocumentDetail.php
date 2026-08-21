<?php

namespace App\Livewire\Documents;

use App\Models\Comment;
use App\Models\Document;
use App\Models\DocumentShare;
use App\Models\User;
use App\Services\MarkdownLiteService;
use App\Services\ShareLinkService;
use Livewire\Component;

class DocumentDetail extends Component
{
    public Document $document;

    public string $comment = '';

    public string $shareEmail = '';

    public string $sharePermission = 'viewer';

    public ?string $linkExpiry = null;

    public string $editName = '';

    public string $editDescription = '';

    public string $editVisibility = 'project';

    public ?int $editingCommentId = null;

    public string $editingCommentBody = '';

    public function mount(Document $document): void
    {
        $this->authorize('view', $document);
        $this->document = $document;
        $this->editName = $document->name;
        $this->editDescription = $document->description ?? '';
        $this->editVisibility = $document->visibility;
    }

    public function addComment(MarkdownLiteService $renderer): void
    {
        $this->authorize('create', [Comment::class, $this->document]);
        $data = $this->validate(['comment' => ['required', 'string', 'max:5000']]);
        $this->document->comments()->create(['user_id' => auth()->id(), 'body_raw' => $data['comment'], 'body_rendered' => $renderer->render($data['comment'])]);
        $this->reset('comment');
    }

    public function deleteComment(int $commentId): void
    {
        $comment = $this->document->comments()->findOrFail($commentId);
        $this->authorize('delete', $comment);
        $comment->delete();
    }

    public function beginEditComment(int $commentId): void
    {
        $comment = $this->document->comments()->findOrFail($commentId);
        $this->authorize('update', $comment);
        $this->editingCommentId = $comment->id;
        $this->editingCommentBody = $comment->body_raw;
    }

    public function saveComment(MarkdownLiteService $renderer): void
    {
        $comment = $this->document->comments()->findOrFail($this->editingCommentId);
        $this->authorize('update', $comment);
        $data = $this->validate(['editingCommentBody' => ['required', 'string', 'max:5000']]);
        $comment->update(['body_raw' => $data['editingCommentBody'], 'body_rendered' => $renderer->render($data['editingCommentBody'])]);
        $this->reset(['editingCommentId', 'editingCommentBody']);
    }

    public function shareWithUser(): void
    {
        $this->authorize('share', $this->document);
        $data = $this->validate(['shareEmail' => ['required', 'email', 'exists:users,email'], 'sharePermission' => ['required', 'in:viewer,editor']]);
        $user = User::where('email', $data['shareEmail'])->firstOrFail();
        DocumentShare::updateOrCreate(['document_id' => $this->document->id, 'user_id' => $user->id], ['permission' => $data['sharePermission'], 'shared_by' => auth()->id()]);
        $this->reset('shareEmail');
    }

    public function generateLink(ShareLinkService $service): void
    {
        $this->authorize('share', $this->document);
        $this->validate(['linkExpiry' => ['nullable', 'date', 'after:now']]);
        $service->generate($this->document, auth()->user(), $this->linkExpiry ? now()->parse($this->linkExpiry) : null);
    }

    public function revokeLink(int $linkId): void
    {
        $this->authorize('share', $this->document);
        $this->document->shareLinks()->findOrFail($linkId)->update(['revoked_at' => now()]);
    }

    public function revokeShare(int $shareId): void
    {
        $this->authorize('share', $this->document);
        $this->document->shares()->findOrFail($shareId)->delete();
    }

    public function saveDetails(): void
    {
        $this->authorize('update', $this->document);
        $data = $this->validate(['editName' => ['required', 'string', 'max:255'], 'editDescription' => ['nullable', 'string', 'max:5000'], 'editVisibility' => ['required', 'in:project,private']]);
        $this->document->update(['name' => $data['editName'], 'description' => $data['editDescription'], 'visibility' => $data['editVisibility']]);
        session()->flash('success', 'Document updated.');
    }

    public function deleteDocument()
    {
        $this->authorize('delete', $this->document);
        $project = $this->document->project;
        $this->document->delete();

        return $this->redirectRoute('projects.show', $project, navigate: true);
    }

    public function render()
    {
        $this->document->load(['owner', 'project', 'shares.user', 'shareLinks', 'comments.user', 'activities.user']);

        return view('livewire.documents.document-detail');
    }
}
