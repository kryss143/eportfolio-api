<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('subject_type');
            $table->string('subject_id')->nullable();
            $table->string('event'); // created, updated, deleted
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
            $table->index('event');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log');
    }
};
