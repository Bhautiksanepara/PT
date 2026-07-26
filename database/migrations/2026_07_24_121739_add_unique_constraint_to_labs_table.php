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
        // Unique gst_number constraint removed per client request
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Unique gst_number constraint removed per client request
    }
};
