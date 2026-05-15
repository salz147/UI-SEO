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
        Schema::create('s_e_o_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seo_period_id')->constrained('s_e_o_periods')->cascadeOnDelete();
            $table->string('keyword')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('media_type')->default('text'); // text, image, video, document
            $table->string('media_url')->nullable();
            $table->integer('position')->nullable();
            $table->decimal('traffic', 12, 2)->default(0);
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('seo_period_id');
            $table->index('status');
            $table->index('keyword');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('s_e_o_items');
    }
};
