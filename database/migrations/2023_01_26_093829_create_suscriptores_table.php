<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suscriptores', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('nombre', 191);
            $table->string('apellidos', 191);
            $table->string('dni', 191);
            $table->string('email', 191);
            $table->integer('telefono')->nullable();
            $table->string('sexo', 191);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('suscriptores');
    }
};
