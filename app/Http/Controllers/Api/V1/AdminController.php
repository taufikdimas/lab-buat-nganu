<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Document;
use App\Models\Project;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard(): array
    {
        return ['users' => User::count(), 'projects' => Project::withTrashed()->count(), 'documents' => Document::withTrashed()->count(), 'suspended_users' => User::where('status', 'suspended')->count()];
    }

    public function users(Request $request)
    {
        return User::query()->when($request->status, fn ($q, $status) => $q->where('status', $status))->latest()->paginate();
    }

    public function updateUser(Request $request, User $user): User
    {
        $user->update($request->validate(['name' => ['sometimes', 'string', 'max:255'], 'system_role' => ['sometimes', 'in:admin,user'], 'status' => ['sometimes', 'in:active,suspended']]));
        AuditLog::create(['actor_id' => $request->user()->id, 'action' => 'user.updated', 'target_type' => 'User', 'target_id' => $user->id, 'ip_address' => $request->ip(), 'user_agent' => $request->userAgent()]);

        return $user;
    }

    public function projects()
    {
        return Project::withTrashed()->with('owner')->withCount(['memberships', 'documents'])->latest()->paginate();
    }

    public function documents()
    {
        return Document::withTrashed()->with(['owner', 'project'])->latest()->paginate();
    }

    public function auditLogs()
    {
        return AuditLog::with('actor')->latest()->paginate();
    }

    public function settings()
    {
        return SystemSetting::all()->pluck('value', 'key');
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate(['avatar_allowed_domains' => ['sometimes', 'string', 'max:3000'], 'max_upload_size_mb' => ['sometimes', 'integer', 'min:1', 'max:100']]);
        foreach ($data as $key => $value) {
            SystemSetting::updateOrCreate(['key' => $key], ['value' => (string) $value, 'updated_by' => $request->user()->id]);
        }

        return $this->settings();
    }
}
