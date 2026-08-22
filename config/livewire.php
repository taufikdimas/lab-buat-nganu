<?php

return [
    'component_layout' => 'components.layouts.app',

    // The component applies the administrator-controlled limit. The temporary
    // transport must allow the full range accepted by that setting (1-100 MB),
    // otherwise Livewire rejects larger files before the component can validate it.
    'temporary_file_upload' => [
        'rules' => ['required', 'file', 'max:102400'],
    ],
];
