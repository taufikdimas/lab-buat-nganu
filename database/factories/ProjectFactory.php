<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return ['name' => fake()->catchPhrase(), 'description' => fake()->sentence(14), 'owner_id' => User::factory(), 'status' => 'active'];
    }
}
