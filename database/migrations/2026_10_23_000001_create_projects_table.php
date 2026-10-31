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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_code')->unique();
            $table->string('project_name');
            $table->string('location');
            $table->string('contractor_name');
            $table->date('maintenance_start_date');
            $table->date('maintenance_end_date');
            $table->text('description')->nullable();
            $table->string('status')->default('Masa Pemeliharaan'); // Masa Pemeliharaan, Selesai, Aktif
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
