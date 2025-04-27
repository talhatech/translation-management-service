<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('translation_tag', function (Blueprint $table) {
            $table->foreignUuid('translation_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('tag_id')->constrained()->onDelete('cascade');

            $table->unique(['translation_id', 'tag_id']);
            $table->index(['tag_id', 'translation_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translation_tag');
    }
};
