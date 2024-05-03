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
            $table->string('name')->nullable;
            $table->string('email')->unique();
            $table->string('password');
            $table->string('dob')->default('');
            $table->boolean('is_active')->default(false);
            $table->string('email_verified_at')->default('');
            $table->string('verification_token')->default('');
            $table->longText('data')->nullable();
            $table->string('profile_image')->nullable();
            $table->boolean('self_signup')->default(false);
            $table->rememberToken();
            $table->timestamps();
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
