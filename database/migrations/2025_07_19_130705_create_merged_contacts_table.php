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
        Schema::create('merged_contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contact_id');   // Who was merged (secondary)
            $table->unsignedBigInteger('to_contact_id');     // Who received the merge
            $table->unsignedBigInteger('master_contact_id'); // User-selected master
            $table->timestamp('merged_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merged_contacts');
    }
};
