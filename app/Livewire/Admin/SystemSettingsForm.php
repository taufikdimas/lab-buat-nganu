<?php

namespace App\Livewire\Admin;

use App\Models\SystemSetting;
use Livewire\Component;

class SystemSettingsForm extends Component
{
    public string $avatarDomains = '';

    public int $maxUploadSize = 20;

    public function mount(): void
    {
        $this->avatarDomains = SystemSetting::value('avatar_allowed_domains', '');
        $this->maxUploadSize = (int) SystemSetting::value('max_upload_size_mb', 20);
    }

    public function save(): void
    {
        $data = $this->validate(['avatarDomains' => ['required', 'string', 'max:3000'], 'maxUploadSize' => ['required', 'integer', 'min:1', 'max:100']]);
        SystemSetting::updateOrCreate(['key' => 'avatar_allowed_domains'], ['value' => $data['avatarDomains'], 'updated_by' => auth()->id()]);
        SystemSetting::updateOrCreate(['key' => 'max_upload_size_mb'], ['value' => (string) $data['maxUploadSize'], 'updated_by' => auth()->id()]);
        session()->flash('success', 'Settings saved.');
    }

    public function render()
    {
        return view('livewire.admin.system-settings-form');
    }
}
