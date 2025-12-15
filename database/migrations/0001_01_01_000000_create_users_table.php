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
            $table->id('user_id');
            $table->string('account_type')->default('user');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('fishpond_name')->nullable();
            $table->string('barangay')->nullable();
            $table->string('municipality')->nullable();
            $table->string('province')->nullable();
            $table->decimal('total_fishpond_area', 8, 2)->nullable();
            $table->string('species_cultured')->nullable();
            $table->string('water_type')->nullable();
            $table->string('farm_type')->nullable();
            $table->string('culture_system')->nullable();
            $table->string('culture_method')->nullable();
            $table->string('activities_during_pond_preperation')->nullable();
            $table->string('liming')->nullable();
            $table->string('fertilizer')->nullable();
            $table->string('disinfectant')->nullable();
            $table->string('source_of_fingerlings_larvae')->nullable();
            $table->string('stocking_density')->nullable();
            $table->string('culture_period')->nullable();
            $table->string('harvest_method')->nullable();
            $table->string('total_production_per_cropping')->nullable();
            $table->string('feeds')->nullable();
            $table->string('amount_per_day')->nullable();
            $table->string('frequency')->nullable();
            $table->string('product_destination')->nullable();
            $table->string('api_key')->unique()->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
