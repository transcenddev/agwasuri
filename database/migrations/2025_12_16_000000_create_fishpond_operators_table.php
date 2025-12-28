<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('fishpond_operators', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('pond_name')->nullable();
            $table->string('barangay')->nullable();
            $table->string('municipality')->nullable();
            $table->string('province')->nullable();
            $table->decimal('total_area_ha', 8, 2)->nullable();
            $table->string('species_cultured')->nullable();
            $table->string('water_type')->nullable();
            $table->string('contact')->nullable();
            $table->text('notes')->nullable();
            $table->string('source')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->index(['pond_name','barangay']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('fishpond_operators');
    }
};
