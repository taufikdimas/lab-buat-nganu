<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body_raw');
            $table->text('body_rendered');
            $table->timestamps();
            $table->softDeletes();
            $table->index('document_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
