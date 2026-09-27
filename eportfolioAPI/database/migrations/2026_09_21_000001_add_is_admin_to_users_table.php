<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint as SqlBlueprint;
use Illuminate\Support\Facades\Schema;
use MongoDB\Laravel\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        if ($this->mongoSchema()) {
            // Schemaless: the model simply writes is_admin.
            Schema::table('users', function (Blueprint $collection) {
                //
            });

            return;
        }

        // ---- Legacy SQL schema (test fallback) --------------------------------

        Schema::table('users', function (SqlBlueprint $table) {
            $table->boolean('is_admin')->default(false);
        });
    }

    public function down(): void
    {
        if (! $this->mongoSchema()) {
            Schema::table('users', function (SqlBlueprint $table) {
                $table->dropColumn('is_admin');
            });
        }
    }

    private function mongoSchema(): bool
    {
        return ! app()->environment('testing') && \App\Support\MongoProbe::available();
    }
};
