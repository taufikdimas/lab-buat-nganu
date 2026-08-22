<?php

namespace App\Livewire\Projects;

use App\Models\Document;
use App\Models\Notification;
use App\Models\Project;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\DocumentService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProjectDetail extends Component
{
    use WithFileUploads;

    public Project $project;

    public string $tab = 'documents';

    public $file;

    public string $documentName = '';

    public string $visibility = 'project';

    public string $inviteEmail = '';

    public string $inviteRole = 'viewer';

    public string $editName = '';

    public string $editDescription = '';

    public ?string $membershipRole = null;

    public function mount(Project $project): void
    {
        $this->authorize('view', $project);
        $this->project = $project;
        $this->editName = $project->name;
        $this->editDescription = $project->description ?? '';

        $user = auth()->user();
        if (! $user->isAdmin()) {
            $this->membershipRole = $this->project->memberships()
                ->active()
                ->where('user_id', $user->id)
                ->value('role');
        }
    }

    public function upload(DocumentService $service): void
    {
        $this->storeUpload($service);
    }

    public function updatedFile(): void
    {
        if ($this->file) {
            $this->storeUpload(app(DocumentService::class));
        }
    }

    private function storeUpload(DocumentService $service): void
    {
        $this->authorize('create', [Document::class, $this->project]);
        $max = (int) SystemSetting::value('max_upload_size_mb', 20) * 1024;
        $data = $this->validate(['file' => ['required', 'file', 'max:'.$max], 'documentName' => ['nullable', 'string', 'max:255'], 'visibility' => ['required', 'in:project,private']]);

        // Archived status is intentionally not checked server-side for this training action.
        try {
            $service->store($this->project, auth()->user(), $this->file, ['name' => $data['documentName'] ?: null, 'visibility' => $data['visibility']]);
            $this->reset(['file', 'documentName']);
            session()->flash('success', 'Document uploaded.');
        } catch (\Exception $e) {
            report($e);
            $this->addError('file', 'The document could not be saved. Please try again.');
        }
    }

    public function updatedTab(string $tab): void
    {
        if ($tab !== 'settings') {
            return;
        }

        $this->project->refresh();
        $this->editName = $this->project->name;
        $this->editDescription = $this->project->description ?? '';
    }

    public function invite(): void
    {
        $this->authorize('manageMembers', $this->project);
        $data = $this->validate(['inviteEmail' => ['required', 'email', 'exists:users,email'], 'inviteRole' => ['required', 'in:editor,viewer']]);
        $user = User::where('email', $data['inviteEmail'])->firstOrFail();
        $this->project->memberships()->updateOrCreate(['user_id' => $user->id], ['role' => $data['inviteRole'], 'status' => 'pending', 'invited_by' => auth()->id(), 'invited_at' => now(), 'joined_at' => null]);
        Notification::create(['user_id' => $user->id, 'type' => 'project.invited', 'data' => ['project_id' => $this->project->id, 'message' => auth()->user()->name.' invited you to '.$this->project->name], 'created_at' => now()]);
        $this->reset(['inviteEmail']);
        session()->flash('success', 'Invitation sent.');
    }

    public function archive(): void
    {
        $this->authorize('archive', $this->project);
        $this->project->update(['status' => $this->project->status === 'archived' ? 'active' : 'archived']);
    }

    public function saveSettings(): void
    {
        $this->authorize('update', $this->project);
        $data = $this->validate(['editName' => ['required', 'string', 'max:255'], 'editDescription' => ['nullable', 'string', 'max:5000']]);
        $this->project->update(['name' => $data['editName'], 'description' => $data['editDescription']]);
        $this->project->refresh();
        $this->editName = $this->project->name;
        $this->editDescription = $this->project->description ?? '';
        session()->flash('success', 'Project settings updated.');
    }

    public function changeRole(int $membershipId, string $role): void
    {
        $this->authorize('manageMembers', $this->project);
        abort_unless(in_array($role, ['editor', 'viewer'], true), 422);
        $member = $this->project->memberships()->whereKey($membershipId)->where('role', '!=', 'owner')->firstOrFail();
        $member->update(['role' => $role]);
    }

    public function removeMember(int $membershipId): void
    {
        $this->authorize('manageMembers', $this->project);
        $this->project->memberships()->whereKey($membershipId)->where('role', '!=', 'owner')->firstOrFail()->delete();
    }

    public function transferOwnership(int $membershipId)
    {
        $this->authorize('manageMembers', $this->project);
        $newOwner = $this->project->memberships()->active()->whereKey($membershipId)->where('role', '!=', 'owner')->firstOrFail();
        DB::transaction(function () use ($newOwner) {
            $this->project->memberships()->where('user_id', $this->project->owner_id)->update(['role' => 'editor']);
            $newOwner->update(['role' => 'owner']);
            $this->project->update(['owner_id' => $newOwner->user_id]);
        });

        // Refresh cached role if current user's role changed
        if ($newOwner->user_id !== auth()->id()) {
            $this->membershipRole = 'editor';
        } else {
            $this->membershipRole = 'owner';
        }

        return $this->redirectRoute('projects.show', $this->project, navigate: true);
    }

    public function deleteProject()
    {
        $this->authorize('delete', $this->project);
        $this->project->delete();

        return $this->redirectRoute('projects.index', navigate: true);
    }

    public function render()
    {
        $user = auth()->user();
        $role = $this->membershipRole;

        $canArchive = $user->isAdmin() || $role === 'owner';
        $canUpdate = $user->isAdmin()
            || ($this->project->status === 'active' && $role === 'owner');
        $canManageMembers = $canUpdate;
        $canUpload = $user->isAdmin() || in_array($role, ['owner', 'editor'], true);

        $documents = collect();
        $memberships = collect();
        $activities = collect();

        if ($this->tab === 'documents') {
            $documents = $this->documentsFor($user->id);
        } elseif ($this->tab === 'members') {
            $memberships = $this->project->memberships()
                ->select(['id', 'project_id', 'user_id', 'role', 'status', 'invited_at', 'joined_at'])
                ->with('user:id,name,email,avatar_url')
                ->latest('joined_at')
                ->get();
        } elseif ($this->tab === 'activity') {
            $activities = $this->project->activities()
                ->select(['id', 'project_id', 'user_id', 'description', 'created_at'])
                ->with('user:id,name,avatar_url')
                ->limit(30)
                ->get();
        }

        return view('livewire.projects.project-detail', compact(
            'activities',
            'canArchive',
            'canManageMembers',
            'canUpdate',
            'canUpload',
            'documents',
            'memberships',
        ));
    }

    private function documentsFor(int $userId): Collection
    {
        return Document::query()
            ->select(['id', 'project_id', 'owner_id', 'name', 'size_bytes', 'visibility', 'updated_at'])
            ->where('project_id', $this->project->id)
            ->where(fn ($query) => $query
                ->where('visibility', 'project')
                ->orWhere('owner_id', $userId)
                ->orWhereHas('shares', fn ($share) => $share->where('user_id', $userId)))
            ->with('owner:id,name')
            ->latest()
            ->limit(30)
            ->get();
    }
}
