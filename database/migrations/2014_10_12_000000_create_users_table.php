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
        Schema::create('users', function (Blueprint $table) {
           $table->id();
            $table->integer('id_pegawai')->unique();
            $table->string('password');
            $table->integer('id_role');
            $table->boolean('is_active')->default(1);
            $table->rememberToken();
            $table->timestamps();
            $table->index('id_role');
            $table->foreign('id_pegawai')->references('id')->on('pegawai')->onDelete('cascade')->onUpdate('restrict');
            $table->foreign('id_role')->references('id')->on('roles')->onDelete('cascade')->onUpdate('restrict');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
