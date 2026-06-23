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
        // 1. Time Slots (Slot Waktu)
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();
            $table->integer('slot_number');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_istirahat')->default(false);
            $table->timestamps();
        });

        // 2. Teacher Subject (Target Mengajar / Plotting Awal)
        Schema::create('teacher_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('mapel_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('kelas_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('tahun_ajaran_id')->constrained('academic_years')->onDelete('cascade');
            $table->timestamps();
        });

        // 3. Teacher Availibility (Ketersediaan Guru)
        Schema::create('teacher_availibilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('teachers')->onDelete('cascade');
            $table->enum('day', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']);
            $table->foreignId('time_slot_id')->constrained('time_slots')->onDelete('cascade');
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_slots');
        Schema::dropIfExists('teacher_subjects');
        Schema::dropIfExists('teacher_availibilities');
    }
};
