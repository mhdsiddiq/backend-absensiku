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
        Schema::create('pengajuan_ketidakhadiran', function (Blueprint $table) {
            $table->id();
            $table->integer('id_pegawai');
            $table->integer('id_kategori');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->integer('jumlah_hari');
            $table->text('alasan');
            $table->string('dokumen_pendukung')->nullable();
            $table->enum('status_pengajuan', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->text('catatan_hrd')->nullable();
            $table->integer('disetujui_oleh')->nullable();
            $table->timestamp('tanggal_disetujui')->nullable();
            $table->timestamps();

            // Menambahkan index
            $table->index('id_pegawai');
            $table->index('id_kategori');
            $table->index('disetujui_oleh');

            // Menambahkan foreign key constraints
            $table->foreign('id_pegawai')->references('id')->on('pegawai')
                ->onDelete('cascade')->onUpdate('restrict');
            $table->foreign('id_kategori')->references('id')->on('kategori_ketidakhadiran')
                ->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('disetujui_oleh')->references('id')->on('users')
                ->onDelete('restrict')->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_ketidakhadiran');
    }
};
