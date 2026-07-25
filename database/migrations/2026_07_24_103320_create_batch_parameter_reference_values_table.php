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
        Schema::create('batch_parameter_reference_values', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('batch_id');
            $table->unsignedInteger('program_id');
            $table->unsignedInteger('parameter_id');
            $table->unsignedInteger('replicate_number');
            $table->decimal('reference_value', 18, 6);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('batch_id')->references('batch_id')->on('sample_batches')->onDelete('cascade');
            $table->foreign('program_id')->references('program_id')->on('pt_programs')->onDelete('cascade');
            $table->foreign('parameter_id')->references('parameter_id')->on('program_parameters')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_parameter_reference_values');
    }
};
