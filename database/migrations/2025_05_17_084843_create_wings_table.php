<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Optional: Add foreign key to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('wing_id')->nullable()->constrained('wings')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['wing_id']);
            $table->dropColumn('wing_id');
        });

        Schema::dropIfExists('wings');
    }
};
