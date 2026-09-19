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
        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->string('building_code')->unique();
            $table->string('building_name');
            $table->string('owner_or_responsible_office')->nullable();
            $table->text('address')->nullable();
            $table->string('barangay')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('primary_occupancy')->nullable();
            $table->unsignedSmallInteger('number_of_storeys')->nullable();
            $table->unsignedSmallInteger('year_built')->nullable();
            $table->decimal('approximate_floor_area', 12, 2)->nullable();
            $table->string('building_permit_number')->nullable();
            $table->string('occupancy_permit_number')->nullable();
            $table->string('property_reference_no')->nullable();
            $table->text('remarks')->nullable();
            $table->string('record_status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buildings');
    }
};
