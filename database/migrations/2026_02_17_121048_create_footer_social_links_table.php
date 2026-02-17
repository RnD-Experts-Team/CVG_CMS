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
        Schema::create('footer_social_links', function (Blueprint $table) {
            $table->id();
            $table->enum('platform', ['facebook', 'instagram', 'linkedin', 'whatsapp', 'fountain', 'indeed', 'youtube']);
            $table->string('url', 1024)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique('platform', 'uq_footer_platform');
            $table->index('sort_order', 'idx_footer_social_sort');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('footer_social_links');
    }
};
