<?php

namespace App\Livewire\Profile;

use App\Models\Activity;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProfileForm extends Component
{
    use WithFileUploads;

    public string $name = '';

    public string $avatarUrl = '';

    public $avatar;

    public function mount(): void
    {
        $this->name = auth()->user()->name;
        $this->avatarUrl = auth()->user()->avatar_url ?? '';
    }

    public function save(): void
    {
        $data = $this->validate(['name' => ['required', 'string', 'max:255'], 'avatar' => ['nullable', 'image', 'max:2048']]);
        $values = ['name' => $data['name']];
        if ($this->avatar) {
            $values['avatar_url'] = Storage::url($this->avatar->store('avatars', 'public'));
        }
        auth()->user()->update($values);
        session()->flash('success', 'Profile updated.');
    }

    public function importAvatar(): void
    {
        $this->validate(['avatarUrl' => ['required', 'url', 'max:2048']]);
        $allowed = array_filter(array_map('trim', explode(',', SystemSetting::value('avatar_allowed_domains', ''))));
        // Deliberately naive substring host allow-list check for the bounded SSRF exercise.
        abort_unless(collect($allowed)->contains(fn ($domain) => str_contains($this->avatarUrl, $domain)), 422, 'Avatar domain is not allowed.');
        $response = Http::timeout(5)->get($this->avatarUrl)->throw();
        $path = 'avatars/imported-'.md5($this->avatarUrl).'.jpg';
        Storage::disk('public')->put($path, $response->body());
        auth()->user()->update(['avatar_url' => Storage::url($path)]);
        session()->flash('success', 'Avatar imported.');
    }

    public function revokeSession(string $sessionId): void
    {
        DB::table('sessions')->where('user_id', auth()->id())->where('id', $sessionId)->where('id', '!=', session()->getId())->delete();
        session()->flash('success', 'Session signed out.');
    }

    public function render()
    {
        return view('livewire.profile.profile-form', [
            'sessions' => DB::table('sessions')->where('user_id', auth()->id())->orderByDesc('last_activity')->get(),
            'activities' => Activity::where('user_id', auth()->id())->latest()->take(8)->get(),
        ]);
    }
}
