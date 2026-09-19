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
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->string('assessment_number')->unique();
            $table->foreignId('building_id')->constrained()->restrictOnDelete();
            $table->date('assessment_date');
            $table->time('assessment_time')->nullable();
            $table->foreignId('assessor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('assessment_type');
            $table->string('assessment_level');
            $table->unsignedBigInteger('fema_version_id')->nullable();
            $table->string('status')->default('Draft');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
