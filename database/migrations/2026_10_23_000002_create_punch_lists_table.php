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
        Schema::create('punch_lists', function (Blueprint $table) {
            $table->id();
            $table->string('punch_code')->unique();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('category')->default('Elektrikal');
            $table->string('location_detail')->nullable();
            $table->string('priority')->default('Sedang'); // Rendah, Sedang, Tinggi, Kritis
            $table->string('status')->default('Baru'); // Baru, Ditugaskan, Dalam Proses, Menunggu Verifikasi, Selesai, Ditolak Saat Verifikasi
            $table->foreignId('pic_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('finding_date');
            $table->date('due_date');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('punch_lists');
    }
};
