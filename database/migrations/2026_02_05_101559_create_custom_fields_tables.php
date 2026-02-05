<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_field_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('model_type'); // Ej: App\Models\Product
            $table->string('name'); // Nombre único del campo (slug)
            $table->string('label'); // Etiqueta visible en UI
            $table->string('type')->default('string'); // string, number, text, boolean, date, datetime, select
            $table->json('options')->nullable(); // Para selects: ["option1", "option2"], configuraciones adicionales
            $table->json('rules')->nullable(); // Reglas de validación: {"required": true, "min": 5}
            $table->integer('order')->default(0); // Orden de aparición
            $table->boolean('is_active')->default(true);
            $table->boolean('show_in_table')->default(false); // Mostrar en tabla
            $table->timestamps();

            $table->unique(['model_type', 'name']);
            $table->index('model_type');
        });

        Schema::create('custom_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_field_definition_id')->constrained('custom_field_definitions')->onDelete('cascade');
            $table->string('model_type'); // Polimórfico
            $table->unsignedBigInteger('model_id');
            $table->text('value')->nullable(); // Valor almacenado (JSON para tipos complejos)
            $table->timestamps();

            $table->unique(['custom_field_definition_id', 'model_type', 'model_id'], 'custom_field_values_unique');
            $table->index(['model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_field_values');
        Schema::dropIfExists('custom_field_definitions');
    }
};
