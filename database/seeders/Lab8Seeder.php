<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Lab8Seeder extends Seeder
{
    public function run()
    {
        $existingSample = DB::table('samples')->where('registration_id', 20)->first();

        if (!$existingSample) {
            $sampleId = DB::table('samples')->insertGetId([
                'sample_code' => 'PT-2026-020',
                'program_id' => 8,
                'registration_id' => 20,
                'batch_id' => null,
                'qr_code' => 'QR-PT-2026-020',
                'status' => 'dispatched',
                'created_at' => now(),
            ]);

            DB::table('dispatches')->insert([
                'sample_id' => $sampleId,
                'dispatch_date' => date('Y-m-d'),
                'courier_name' => 'DTDC Express',
                'tracking_number' => 'DTDC-992011',
                'dispatched_by' => 1,
                'notification_sent' => 1,
                'notification_sent_at' => now(),
                'created_at' => now(),
            ]);
        } else {
            $sampleId = $existingSample->sample_id;
        }

        DB::table('statistical_results')->updateOrInsert(
            ['program_id' => 8, 'parameter_id' => 38],
            [
                'mean' => 7.45,
                'median' => 7.45,
                'standard_deviation' => 0.20,
                'robust_mean' => 7.45,
                'robust_standard_deviation' => 0.20,
                'assigned_value' => 7.45,
                'calculated_at' => now(),
            ]
        );

        $existingObs = DB::table('observations')->where('registration_id', 20)->first();
        if (!$existingObs) {
            DB::table('observations')->insert([
                'sample_id' => $sampleId,
                'registration_id' => 20,
                'lab_id' => 8,
                'parameter_id' => 38,
                'test_method' => 'IS 3025',
                'result_value' => '7.50',
                'unit' => 'MICRON unit',
                'remarks' => 'QC evaluation completed with satisfactory performance.',
                'is_locked' => 1,
                'submitted_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $existingReport = DB::table('reports')->where('registration_id', 20)->first();
        if (!$existingReport) {
            DB::table('reports')->insert([
                'report_type' => 'individual',
                'program_id' => 8,
                'registration_id' => 20,
                'parameter_id' => null,
                'file_path' => 'uploads/reports/individual_report_P8_R20.pdf',
                'qr_code' => 'QR-IND-PROG8-REG20',
                'digital_signature' => 'SHA256-SIG-BHAUTIK-REG20',
                'generated_at' => now(),
            ]);
        }
    }
}
