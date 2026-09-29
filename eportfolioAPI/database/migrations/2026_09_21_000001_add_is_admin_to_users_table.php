<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MongoDB\Laravel\Schema\Blueprint;

return new class extends Migration
{
    // Users live on MongoDB as schemaless documents; is_admin is simply a
    // field the model writes. No schema change is required — kept as a no-op
    // for migration history continuity.
    public function up(): void
    {
        //
    }

    public function down(): void
    {
        //
    }
};
