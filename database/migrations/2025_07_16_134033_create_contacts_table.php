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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED AUTO_INCREMENT
            $table->string('name', 100); // VARCHAR(100)
            $table->string('email', 150); // VARCHAR(150)
            $table->string('phone', 20)->nullable(); // VARCHAR(20), can be null
            $table->enum('gender', ['male', 'female'])->nullable(); // ENUM
            $table->string('profile_image', 255)->nullable(); // VARCHAR(255)
            $table->string('additional_file', 255)->nullable(); // VARCHAR(255)
            $table->timestamps(); // created_at, updated_at (DATETIME)
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
