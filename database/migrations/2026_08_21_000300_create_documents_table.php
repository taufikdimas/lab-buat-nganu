<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('file_path', 2048);
            $table->string('original_filename');
            $table->string('mime_type', 150);
            $table->unsignedBigInteger('size_bytes');
            $table->enum('visibility', ['project', 'private'])->default('project');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['project_id', 'visibility']);
            $table->index('owner_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
