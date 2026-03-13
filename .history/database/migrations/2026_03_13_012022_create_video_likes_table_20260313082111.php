<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_id')->constrained('videos')->cascadeOnDelete();
            $table->string('ip_address'); // hashed before storing
            $table->timestamp('created_at')->nullable();

            $table->unique(['video_id', 'ip_address']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_likes');
    }
};
