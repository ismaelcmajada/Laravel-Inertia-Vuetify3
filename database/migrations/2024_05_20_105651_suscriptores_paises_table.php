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
        Schema::create('suscriptores_paises', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('suscriptor_id');
            $table->integer('pais_id');
            $table->integer('people');
            $table->timestamps();

            $table->unique(['suscriptor_id', 'pais_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suscriptores_paises');
    }
};
