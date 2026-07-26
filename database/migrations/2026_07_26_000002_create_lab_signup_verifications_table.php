<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_signup_verifications', function (Blueprint $table) {
            $table->increments('id');
            $table->string('contact_person', 150);
            $table->string('laboratory_name', 255)->nullable();
            $table->string('center_name', 255)->nullable();
            $table->string('email', 150)->unique();
            $table->text('address');
            $table->string('city', 100);
            $table->string('state', 100);
            $table->string('country', 100);
            $table->string('pin_code', 20);
            $table->string('mobile_number', 20);
            $table->string('otp_hash');
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_signup_verifications');
    }
};
