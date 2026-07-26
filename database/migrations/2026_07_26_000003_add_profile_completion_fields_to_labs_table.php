<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('labs', function (Blueprint $table) {
            $table->string('center_name', 255)->nullable()->after('laboratory_name');
            $table->timestamp('profile_completed_at')->nullable()->after('status');
        });

        // Preserve access for accounts created before the two-stage onboarding flow.
        DB::table('labs')->whereNull('profile_completed_at')->update([
            'center_name' => DB::raw('laboratory_name'),
            'profile_completed_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('labs', function (Blueprint $table) {
            $table->dropColumn(['center_name', 'profile_completed_at']);
        });
    }
};
