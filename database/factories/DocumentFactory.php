<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        $name = fake()->words(3, true);

        return ['project_id' => Project::factory(), 'owner_id' => User::factory(), 'name' => ucfirst($name), 'file_path' => 'documents/demo/'.$name.'.txt', 'original_filename' => $name.'.txt', 'mime_type' => 'text/plain', 'size_bytes' => fake()->numberBetween(800, 500000), 'visibility' => 'project'];
    }
}
