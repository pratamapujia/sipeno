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
        // 1. Teachers (Guru)
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('users_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('nip')->unique()->nullable();
            $table->string('nama_guru');
            $table->enum('jenis_kelamin',['L', 'P']);
            $table->enum('status', ['Tetap', 'Honorer'])->default('Tetap');
            $table->timestamps();
        });

        // 2. Subjects (Mata Pelajaran)
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('kode_mapel')->unique();
            $table->string('nama_mapel');
            $table->integer('beban_jam'); // Jumlah jam per minggu
            $table->timestamps();
        });

        // 3. Classes (Kelas)
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('kelas')->unique();
            $table->string('tingkat');
            $table->timestamps();
        });

        // 4. Rooms (Ruangan)
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('nama_ruangan')->unique();
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });

        // 5. Academic Years (Tahun Ajaran)
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('tahun_ajaran');
            $table->enum('semester', ['Ganjil', 'Genap']);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('academic_years');
    }
};
