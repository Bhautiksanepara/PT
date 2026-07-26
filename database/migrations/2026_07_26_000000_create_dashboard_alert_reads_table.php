<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboard_alert_reads', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('lab_id');
            $table->unsignedInteger('program_id');
            $table->string('alert_type', 50);
            $table->timestamp('read_at')->useCurrent();

            $table->unique(['lab_id', 'program_id', 'alert_type']);
            $table->foreign('lab_id')->references('lab_id')->on('labs')->onDelete('cascade');
            $table->foreign('program_id')->references('program_id')->on('pt_programs')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_alert_reads');
    }
};
