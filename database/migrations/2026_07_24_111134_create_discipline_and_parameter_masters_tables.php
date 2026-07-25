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
        // 1. Create discipline_masters
        Schema::create('discipline_masters', function (Blueprint $table) {
            $table->increments('id');
            $table->string('discipline_name', 150)->unique();
            $table->string('short_code', 10)->unique();
            $table->timestamp('created_at')->useCurrent();
        });

        // 2. Create parameter_masters
        Schema::create('parameter_masters', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('discipline_id');
            $table->string('parameter_name', 150);
            $table->string('test_method', 150)->nullable();
            $table->string('unit', 50)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('discipline_id')->references('id')->on('discipline_masters')->onDelete('cascade');
            $table->unique(['discipline_id', 'parameter_name', 'test_method', 'unit'], 'unique_parameter_details');
        });

        // 3. Seed default master data
        $chemicalId = DB::table('discipline_masters')->insertGetId([
            'discipline_name' => 'Chemical',
            'short_code' => 'CHEM',
            'created_at' => now(),
        ]);
        $biologicalId = DB::table('discipline_masters')->insertGetId([
            'discipline_name' => 'Biological',
            'short_code' => 'BIOL',
            'created_at' => now(),
        ]);
        $mechanicalId = DB::table('discipline_masters')->insertGetId([
            'discipline_name' => 'Mechanical',
            'short_code' => 'MECH',
            'created_at' => now(),
        ]);

        // Seed default parameters
        $defaultParams = [
            ['discipline_id' => $chemicalId, 'parameter_name' => 'pH', 'test_method' => 'Electrometric', 'unit' => 'pH unit'],
            ['discipline_id' => $chemicalId, 'parameter_name' => 'Moisture', 'test_method' => 'Gravimetric', 'unit' => '%'],
            ['discipline_id' => $chemicalId, 'parameter_name' => 'Conductivity', 'test_method' => 'Conductometry', 'unit' => 'µS/cm'],
            ['discipline_id' => $biologicalId, 'parameter_name' => 'Coliforms', 'test_method' => 'MPN Method', 'unit' => 'MPN/100ml'],
            ['discipline_id' => $biologicalId, 'parameter_name' => 'Yeast & Mold', 'test_method' => 'Plate Count', 'unit' => 'CFU/g'],
            ['discipline_id' => $biologicalId, 'parameter_name' => 'Salmonella', 'test_method' => 'Detection Method', 'unit' => 'Absent/Present'],
            ['discipline_id' => $mechanicalId, 'parameter_name' => 'Tensile Strength', 'test_method' => 'UTM', 'unit' => 'MPa'],
            ['discipline_id' => $mechanicalId, 'parameter_name' => 'Hardness', 'test_method' => 'Rockwell', 'unit' => 'HRB'],
        ];
        foreach ($defaultParams as $param) {
            DB::table('parameter_masters')->insert(array_merge($param, ['created_at' => now()]));
        }

        // 4. Update pt_programs columns
        Schema::table('pt_programs', function (Blueprint $table) {
            $table->unsignedInteger('discipline_id')->nullable()->after('program_name');
        });

        // Migrate existing disciplines
        $programs = DB::table('pt_programs')->get();
        foreach ($programs as $prog) {
            $discText = strtolower(trim($prog->discipline));
            $mappedId = null;
            if (str_contains($discText, 'chem')) {
                $mappedId = $chemicalId;
            } elseif (str_contains($discText, 'biol') || str_contains($discText, 'bio')) {
                $mappedId = $biologicalId;
            } elseif (str_contains($discText, 'mech')) {
                $mappedId = $mechanicalId;
            } else {
                // If not matching, create new discipline automatically
                if (!empty($prog->discipline)) {
                    try {
                        $mappedId = DB::table('discipline_masters')->insertGetId([
                            'discipline_name' => $prog->discipline,
                            'short_code' => strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $prog->discipline), 0, 4)),
                            'created_at' => now(),
                        ]);
                    } catch (\Exception $e) {
                        $mappedId = $chemicalId; // fallback
                    }
                } else {
                    $mappedId = $chemicalId; // fallback
                }
            }

            DB::table('pt_programs')->where('program_id', $prog->program_id)->update([
                'discipline_id' => $mappedId,
            ]);
        }

        // Drop the old text discipline column
        Schema::table('pt_programs', function (Blueprint $table) {
            $table->dropColumn('discipline');
        });

        // Add foreign key constraint
        Schema::table('pt_programs', function (Blueprint $table) {
            $table->foreign('discipline_id')->references('id')->on('discipline_masters')->onDelete('set null');
        });

        // 5. Update pt_programs program_status column values and alter enum
        Schema::table('pt_programs', function (Blueprint $table) {
            $table->string('program_status', 50)->change();
        });
        
        DB::statement("UPDATE pt_programs SET program_status = 'reopen' WHERE program_status = 'open'");
        DB::statement("UPDATE pt_programs SET program_status = 'forcefully_closed' WHERE program_status = 'closed' OR program_status = 'completed'");

        DB::statement("ALTER TABLE pt_programs MODIFY COLUMN program_status ENUM('draft', 'reopen', 'forcefully_closed') NOT NULL DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Alter status column back
        Schema::table('pt_programs', function (Blueprint $table) {
            $table->string('program_status', 50)->change();
        });

        DB::statement("UPDATE pt_programs SET program_status = 'open' WHERE program_status = 'reopen'");
        DB::statement("UPDATE pt_programs SET program_status = 'closed' WHERE program_status = 'forcefully_closed'");

        DB::statement("ALTER TABLE pt_programs MODIFY COLUMN program_status ENUM('draft', 'open', 'closed', 'completed') NOT NULL DEFAULT 'draft'");

        // Drop foreign key and restore discipline column
        Schema::table('pt_programs', function (Blueprint $table) {
            $table->dropForeign(['discipline_id']);
            $table->string('discipline', 150)->nullable()->after('program_name');
        });

        // Restore text values
        $programs = DB::table('pt_programs')->get();
        foreach ($programs as $prog) {
            if ($prog->discipline_id) {
                $disc = DB::table('discipline_masters')->where('id', $prog->discipline_id)->first();
                if ($disc) {
                    DB::table('pt_programs')->where('program_id', $prog->program_id)->update([
                        'discipline' => $disc->discipline_name,
                    ]);
                }
            }
        }

        Schema::table('pt_programs', function (Blueprint $table) {
            $table->dropColumn('discipline_id');
        });

        Schema::dropIfExists('parameter_masters');
        Schema::dropIfExists('discipline_masters');
    }
};
