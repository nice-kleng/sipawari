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
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('rater_id')->constrained()->onDelete('cascade');

            // Rating scores (1-5)
            $table->tinyInteger('overall_satisfaction')->unsigned();
            $table->tinyInteger('friendliness')->unsigned()->nullable();
            $table->tinyInteger('professionalism')->unsigned()->nullable();
            $table->tinyInteger('service_speed')->unsigned()->nullable();

            // Comment
            $table->text('comment')->nullable();

            // Metadata
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->boolean('is_approved')->default(true);
            $table->boolean('is_flagged')->default(false);
            $table->text('flag_reason')->nullable();

            $table->timestamps();

            // Indexes for performance
            $table->index(['employee_id', 'created_at']);
            $table->index(['rater_id', 'employee_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
