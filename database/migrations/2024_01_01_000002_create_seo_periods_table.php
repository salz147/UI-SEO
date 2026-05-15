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
        Schema::create('s_e_o_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('s_e_o_id')->constrained('s_e_o_s')->cascadeOnDelete();
            $table->integer('month');
            $table->integer('year');
            $table->integer('month_active')->default(0);
            $table->string('status')->default('pending'); // pending, active, completed, overdue
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->date('paid_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('s_e_o_id');
            $table->index('status');
            $table->unique(['s_e_o_id', 'month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('s_e_o_periods');
    }
};
