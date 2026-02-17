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
        Schema::create('values_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('values_section_id')->constrained('values_section')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('media_id')->nullable()->constrained('media')->onDelete('set null');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('values_items');
    }
};
