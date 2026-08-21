<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('document_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('action', 150);
            $table->string('description', 500);
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index('project_id');
            $table->index('document_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
