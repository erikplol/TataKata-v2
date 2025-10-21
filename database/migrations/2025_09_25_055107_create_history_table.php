<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('history', function (Blueprint $table) {
            $table->id();
            
            // FK ke tabel users
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Opsional: FK ke dokumen terkait
            $table->foreignId('document_id')->nullable()->constrained('documents')->onDelete('set null');

            $table->string('activity_type'); // Contoh: 'Upload', 'Login', 'Cancel'
            $table->text('details'); // Deskripsi lengkap peristiwa
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('history');
    }
};