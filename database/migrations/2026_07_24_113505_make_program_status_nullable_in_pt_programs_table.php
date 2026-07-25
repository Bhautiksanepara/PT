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
        DB::statement("ALTER TABLE pt_programs MODIFY COLUMN program_status ENUM('draft', 'reopen', 'forcefully_closed') NULL DEFAULT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE pt_programs SET program_status = 'draft' WHERE program_status IS NULL");
        DB::statement("ALTER TABLE pt_programs MODIFY COLUMN program_status ENUM('draft', 'reopen', 'forcefully_closed') NOT NULL DEFAULT 'draft'");
    }
};
