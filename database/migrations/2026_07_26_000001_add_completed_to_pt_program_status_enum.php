<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE pt_programs MODIFY COLUMN program_status ENUM('draft', 'reopen', 'forcefully_closed', 'completed') NULL DEFAULT NULL");
    }

    public function down(): void
    {
        DB::statement("UPDATE pt_programs SET program_status = 'forcefully_closed' WHERE program_status = 'completed'");
        DB::statement("ALTER TABLE pt_programs MODIFY COLUMN program_status ENUM('draft', 'reopen', 'forcefully_closed') NULL DEFAULT NULL");
    }
};
