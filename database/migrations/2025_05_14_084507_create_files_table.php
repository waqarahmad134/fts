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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('file_no')->unique();
            $table->string('subject');
            $table->text('puc_proposal')->nullable();

            $table->string('file_image')->nullable();   
            $table->string('file_attachment')->nullable(); 

            $table->enum('status', ['pending', 'closed', 'reopened'])->default('pending');

            $table->foreignId('created_by')->constrained('users');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
