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
        // 1. Schedule Batches (Menampung versi draft simulasi Algoritma Genetika)
        Schema::create('schedule_batches', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->enum('status', ['draft', 'active', 'failed'])->default('draft');
            $table->float('final_fitness_score')->default(0); // Nilai penentu kualitas jadwal
            $table->timestamps();
        });

        // 2. Schedules (Hasil Plotting Akhir)
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_batch_id')->constrained('schedule_batches')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->enum('day', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']);
            $table->foreignId('time_slot_id')->constrained('time_slots')->onDelete('cascade');
            $table->foreignId('classes_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('room_id')->nullable()->constrained('rooms')->onDelete('set null');
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->timestamps();

            // PENTING: Index Unik untuk mencegah bentrok data di level database (Double Protection)
            $table->unique(['schedule_batch_id', 'day', 'time_slot_id', 'classes_id'], 'unique_class_schedule');
            $table->unique(['schedule_batch_id', 'day', 'time_slot_id', 'teacher_id'], 'unique_teacher_schedule');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_batches');
        Schema::dropIfExists('schedules');
    }
};
