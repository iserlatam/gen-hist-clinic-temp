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
        Schema::create('clinical_records', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_documento', 20)->nullable()->unique();   // HC-DOC-00999
            $table->string('estado', 15)->default('borrador')->index();     // borrador | finalizada
            $table->timestamp('finalizada_at')->nullable();

            // ── 1. DATOS GENERALES ─────────────────────────────────────────
            $table->string('paciente_nombre', 150);
            $table->string('doc_identificacion', 30)->index();
            $table->date('fecha_nacimiento')->nullable();
            $table->char('sexo', 1)->nullable();
            $table->string('numero_episodio', 30)->nullable()->index();
            $table->string('aseguradora_entidad', 150)->nullable();
            $table->string('numero_historia_clinica', 30)->nullable()->index();
            $table->string('medico_tratante', 150)->nullable();
            $table->string('especialidad_servicio', 100)->nullable();

            // ── 2. ATENCIÓN CLÍNICA ────────────────────────────────────────
            $table->string('tipo_atencion', 30)->nullable();
            $table->string('tipo_evento', 30)->nullable();
            $table->string('via_ingreso', 30)->nullable();
            $table->string('nivel_triage', 15)->nullable();

            // Anamnesis
            $table->date('fecha_atencion')->nullable();
            $table->time('hora_atencion')->nullable();
            $table->text('motivo_consulta')->nullable();
            $table->text('enfermedad_actual')->nullable();
            $table->text('examen_fisico')->nullable();
            $table->text('rayos_x_imagenologia')->nullable();
            $table->text('plan_conducta')->nullable();

            // Antecedentes
            $table->string('antecedentes_alergicos')->nullable();
            $table->string('antecedentes_farmacologicos')->nullable();
            $table->string('antecedentes_patologicos')->nullable();
            $table->string('antecedentes_quirurgicos')->nullable();

            // Profesional responsable (firma electrónica)
            $table->string('profesional_responsable_nombre', 150)->nullable();
            $table->string('profesional_responsable_especialidad', 100)->nullable();
            $table->string('profesional_responsable_cedula', 20)->nullable();
            $table->string('profesional_responsable_rm', 30)->nullable();
            $table->boolean('firma_electronica')->default(true);            // "Válido como Firma Electrónica"

            // Solo en el PDF (no estaba en el JSON)
            $table->string('factor_aislamiento', 100)->nullable();

            // ── 3. DIAGNÓSTICOS & CONDUCTA ─────────────────────────────────
            $table->string('diagnostico_cie10_codigo', 10)->nullable()->index();
            $table->string('diagnostico_cie10_descripcion', 255)->nullable();
            $table->text('analisis_y_conducta')->nullable();
            $table->text('examen_fisico_control')->nullable();
            $table->text('plan_manejo_incapacidad_texto')->nullable();

            // ── 4. INCAPACIDAD (1:1, nullable si no aplica) ────────────────
            $table->date('incapacidad_fecha_inicio')->nullable();
            $table->date('incapacidad_fecha_fin')->nullable();
            $table->unsignedSmallInteger('incapacidad_dias')->nullable();
            $table->boolean('incapacidad_es_prorroga')->default(false);
            $table->string('incapacidad_tipo', 30)->nullable();
            $table->string('incapacidad_clase', 30)->nullable();
            $table->string('incapacidad_diagnostico_cie10', 255)->nullable();

            // Pie de página institucional ("Impreso por")
            $table->string('impreso_por', 30)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // Órdenes generales (PDF pág. 2) + fórmulas/órdenes ambulatorias (JSON sección 4)
        Schema::create('clinical_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinical_record_id')->constrained()->cascadeOnDelete();
            $table->string('tipo', 10)->default('formula')->index();        // orden | formula
            $table->dateTime('fecha_hora');
            $table->string('codigo', 20);
            $table->string('nombre_prescripcion', 255);
            $table->text('instrucciones')->nullable();
            $table->string('unidad_organizativa', 100)->nullable();
            $table->string('medico_responsable', 150)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinical_orders');
        Schema::dropIfExists('clinical_records');
    }
};
