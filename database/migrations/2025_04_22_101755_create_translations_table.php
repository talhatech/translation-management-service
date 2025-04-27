<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key');
            $table->text('value');
            $table->foreignUuid('language_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['key', 'language_id']);
        });

        DB::statement('ALTER TABLE translations ADD INDEX translations_value_index (value(191))');

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
