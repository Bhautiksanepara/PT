<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Default Admin User
        $admin = AdminUser::updateOrCreate(
            ['username' => 'admin'],
            [
                'full_name' => 'Dr. System Administrator',
                'email' => 'admin@ptsoftware.com',
                'password_hash' => Hash::make('password123'),
                'status' => 'active'
            ]
        );

        // 2. Sample Laboratories (6 Labs for realistic Statistical distribution)
        $labsData = [
            [
                'email' => 'rajesh@apexlabs.com',
                'laboratory_name' => 'Apex Analytical Services',
                'nabl_certificate_number' => 'TC-8912',
                'laboratory_type' => 'Commercial Laboratory',
                'gst_number' => '27AAACA1234A1Z5',
                'city' => 'Mumbai', 'state' => 'Maharashtra', 'contact_person' => 'Dr. Rajesh Sharma',
                'username' => 'lab_apex',
            ],
            [
                'email' => 'sunita@qualitest.org',
                'laboratory_name' => 'QualiTest Research Lab',
                'nabl_certificate_number' => 'TC-5431',
                'laboratory_type' => 'Research Institute',
                'gst_number' => '07BBBCB5678B1Z2',
                'city' => 'Gurugram', 'state' => 'Haryana', 'contact_person' => 'Sunita Patel',
                'username' => 'lab_qualitest',
            ],
            [
                'email' => 'contact@metrolabs.in',
                'laboratory_name' => 'Metro Testing Services',
                'nabl_certificate_number' => 'TC-3321',
                'laboratory_type' => 'Industrial Testing',
                'gst_number' => '33CCCCD9999C1Z8',
                'city' => 'Chennai', 'state' => 'Tamil Nadu', 'contact_person' => 'K. Ramanathan',
                'username' => 'lab_metro',
            ],
            [
                'email' => 'info@biotechlabs.com',
                'laboratory_name' => 'BioTech Research Labs',
                'nabl_certificate_number' => 'TC-7744',
                'laboratory_type' => 'Biotech & Pharma',
                'gst_number' => '29DDDDD8888D1Z4',
                'city' => 'Bengaluru', 'state' => 'Karnataka', 'contact_person' => 'Dr. Ananya Roy',
                'username' => 'lab_biotech',
            ],
            [
                'email' => 'quality@envirocheck.in',
                'laboratory_name' => 'EnviroCheck Laboratories',
                'nabl_certificate_number' => 'TC-6611',
                'laboratory_type' => 'Environmental Testing',
                'gst_number' => '19EEEEE7777E1Z1',
                'city' => 'Kolkata', 'state' => 'West Bengal', 'contact_person' => 'Subhashish Das',
                'username' => 'lab_enviro',
            ],
            [
                'email' => 'tech@universaltest.com',
                'laboratory_name' => 'Universal Precision Testing',
                'nabl_certificate_number' => 'TC-9988',
                'laboratory_type' => 'Commercial Testing',
                'gst_number' => '03FFFFF6666F1Z9',
                'city' => 'Chandigarh', 'state' => 'Punjab', 'contact_person' => 'Vikram Singh',
                'username' => 'lab_universal',
            ],
        ];

        $labIds = [];
        foreach ($labsData as $ld) {
            DB::table('labs')->updateOrInsert(
                ['email' => $ld['email']],
                [
                    'laboratory_name' => $ld['laboratory_name'],
                    'nabl_certificate_number' => $ld['nabl_certificate_number'],
                    'laboratory_type' => $ld['laboratory_type'],
                    'gst_number' => $ld['gst_number'],
                    'address' => 'Industrial Area Phase I',
                    'city' => $ld['city'],
                    'state' => $ld['state'],
                    'country' => 'India',
                    'pin_code' => '400001',
                    'contact_person' => $ld['contact_person'],
                    'designation' => 'Quality Manager',
                    'mobile_number' => '+91 9876543210',
                    'username' => $ld['username'],
                    'password_hash' => Hash::make('password123'),
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            $labIds[] = DB::table('labs')->where('email', $ld['email'])->value('lab_id');
        }

        // 3. Current Active PT Program (2026 Chemical)
        DB::table('pt_programs')->updateOrInsert(
            ['program_code' => 'PT-CHEM-2026-01'],
            [
                'program_name' => 'Chemical Analysis in Water Samples (ISO 17025)',
                'discipline' => 'Chemical',
                'scheme_code' => 'SCH-CHEM-01',
                'description' => 'Proficiency testing for pH, Conductivity, and Heavy Metals determination in drinking water.',
                'program_fee' => 15000.00,
                'registration_start_date' => now()->subDays(15),
                'registration_end_date' => now()->addDays(15),
                'dispatch_date' => now()->addDays(20),
                'submission_deadline' => now()->addDays(40),
                'report_date' => now()->addDays(55),
                'registration_status' => 'active',
                'program_status' => 'open',
                'created_by' => $admin->admin_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        $prog1Id = DB::table('pt_programs')->where('program_code', 'PT-CHEM-2026-01')->value('program_id');

        // Program 1 Parameters
        DB::table('program_parameters')->where('program_id', $prog1Id)->delete();
        DB::table('program_parameters')->insert([
            ['program_id' => $prog1Id, 'parameter_name' => 'pH Value', 'test_method' => 'IS 3025 (Part 11)', 'unit' => 'pH Units', 'created_at' => now()],
            ['program_id' => $prog1Id, 'parameter_name' => 'Electrical Conductivity', 'test_method' => 'IS 3025 (Part 14)', 'unit' => 'µS/cm', 'created_at' => now()],
            ['program_id' => $prog1Id, 'parameter_name' => 'Total Dissolved Solids (TDS)', 'test_method' => 'IS 3025 (Part 16)', 'unit' => 'mg/L', 'created_at' => now()]
        ]);

        $paramPh = DB::table('program_parameters')->where('program_id', $prog1Id)->where('parameter_name', 'pH Value')->value('parameter_id');
        $paramEc = DB::table('program_parameters')->where('program_id', $prog1Id)->where('parameter_name', 'Electrical Conductivity')->value('parameter_id');

        // PT Plan for Program 1
        DB::table('pt_plans')->updateOrInsert(
            ['program_id' => $prog1Id],
            [
                'program_number' => 'PT-PLAN-2026-001',
                'material' => 'Drinking Water Matrix',
                'timeline' => 'Jan 2026 - Mar 2026',
                'assigned_coordinator' => $admin->admin_id,
                'sample_quantity' => '2 Polyethylene Bottles (500ml each)',
                'sample_preparation_instructions' => "1. Store samples at 2-8°C prior to dispatch.\n2. Perform homogeneity testing per ISO 13528.\n3. Include instruction sheet and safety data sheet (SDS) in dispatch box.",
                'created_at' => now(),
            ]
        );

        // Registrations & Payments for Program 1
        $regIds = [];
        foreach ($labIds as $idx => $lId) {
            $regNum = 'REG-2026-000' . ($idx + 1);
            DB::table('program_registrations')->updateOrInsert(
                ['registration_number' => $regNum],
                [
                    'program_id' => $prog1Id,
                    'lab_id' => $lId,
                    'sample_quantity' => '2 Bottles (500ml each)',
                    'shipping_address' => 'Industrial Area Phase I',
                    'billing_address' => 'Industrial Area Phase I',
                    'discount_applied' => 0.00,
                    'status' => 'confirmed',
                    'registered_at' => now()->subDays(5 - $idx),
                ]
            );
            $rId = DB::table('program_registrations')->where('registration_number', $regNum)->value('registration_id');
            $regIds[] = $rId;

            DB::table('payments')->updateOrInsert(
                ['registration_id' => $rId],
                [
                    'amount' => 15000.00,
                    'discount_amount' => 0.00,
                    'final_amount' => 15000.00,
                    'payment_method' => 'NEFT / Bank Transfer',
                    'transaction_id' => 'TXN' . (987654320 + $idx),
                    'payment_status' => 'success',
                    'paid_at' => now()->subDays(5 - $idx),
                    'created_at' => now()->subDays(5 - $idx),
                ]
            );
        }

        // Sample Batch
        DB::table('sample_batches')->updateOrInsert(
            ['batch_number' => 'BATCH-CHEM-2026-01'],
            [
                'program_id' => $prog1Id,
                'material' => 'Drinking Water Matrix',
                'quantity' => '50 Bottles (500ml each)',
                'prepared_by' => $admin->admin_id,
                'verified_by' => $admin->admin_id,
                'preparation_date' => now()->subDays(7)->toDateString(),
                'status' => 'approved',
                'created_at' => now()->subDays(7),
            ]
        );
        $batch1Id = DB::table('sample_batches')->where('batch_number', 'BATCH-CHEM-2026-01')->value('batch_id');

        // Assigned Sample Codes
        $sampleIds = [];
        foreach ($regIds as $idx => $rId) {
            $sampleCode = 'PT-2026-00' . ($idx + 1);
            DB::table('samples')->updateOrInsert(
                ['sample_code' => $sampleCode],
                [
                    'program_id' => $prog1Id,
                    'registration_id' => $rId,
                    'batch_id' => $batch1Id,
                    'qr_code' => 'QR-' . $sampleCode,
                    'status' => 'dispatched',
                    'created_at' => now()->subDays(2),
                ]
            );
            $sampleIds[] = DB::table('samples')->where('sample_code', $sampleCode)->value('sample_id');
        }

        // Dispatches
        foreach ($sampleIds as $idx => $sId) {
            DB::table('dispatches')->updateOrInsert(
                ['sample_id' => $sId],
                [
                    'dispatch_date' => now()->subDays(1)->toDateString(),
                    'courier_name' => $idx % 2 === 0 ? 'BlueDart Express' : 'DTDC Courier',
                    'tracking_number' => 'BD-2026-98765' . $idx,
                    'dispatched_by' => $admin->admin_id,
                    'notification_sent' => 1,
                    'notification_sent_at' => now()->subDays(1),
                    'created_at' => now()->subDays(1),
                ]
            );
        }

        // Observations for pH Value
        $phResults = [
            ['val' => '7.42', 'locked' => 0, 'remarks' => 'Glass electrode pH meter at 25°C.'],
            ['val' => '7.38', 'locked' => 1, 'remarks' => 'SOP-CHEM-04 method.'],
            ['val' => '7.40', 'locked' => 0, 'remarks' => 'Triplicate reading average.'],
            ['val' => '7.45', 'locked' => 0, 'remarks' => 'Calibrated with pH 4, 7, 10 buffers.'],
            ['val' => '7.58', 'locked' => 0, 'remarks' => 'Slight drift observed during test.'],
            ['val' => '6.95', 'locked' => 1, 'remarks' => 'Uncalibrated electrode error.'],
        ];

        DB::table('observations')->where('parameter_id', $paramPh)->delete();

        foreach ($phResults as $idx => $res) {
            $obsId = DB::table('observations')->insertGetId([
                'sample_id' => $sampleIds[$idx],
                'registration_id' => $regIds[$idx],
                'lab_id' => $labIds[$idx],
                'parameter_id' => $paramPh,
                'test_method' => 'IS 3025 (Part 11)',
                'result_value' => $res['val'],
                'unit' => 'pH Units',
                'remarks' => $res['remarks'],
                'is_locked' => $res['locked'],
                'submitted_at' => now()->subHours(6 - $idx),
                'created_at' => now()->subHours(6 - $idx),
                'updated_at' => now()->subHours(6 - $idx),
            ]);

            if ($idx === 0) {
                DB::table('observation_files')->insert([
                    'observation_id' => $obsId,
                    'file_path' => 'uploads/observations/raw_data_ph_apex.pdf',
                    'original_filename' => 'Apex_pH_Calibration_Certificate.pdf',
                    'uploaded_at' => now()->subHours(6),
                ]);
            }
        }

        // EC Results
        $ecResults = ['450.5', '448.0', '452.1', '449.0', '465.0', '410.0'];
        DB::table('observations')->where('parameter_id', $paramEc)->delete();

        foreach ($ecResults as $idx => $val) {
            DB::table('observations')->insert([
                'sample_id' => $sampleIds[$idx],
                'registration_id' => $regIds[$idx],
                'lab_id' => $labIds[$idx],
                'parameter_id' => $paramEc,
                'test_method' => 'IS 3025 (Part 14)',
                'result_value' => $val,
                'unit' => 'µS/cm',
                'remarks' => 'Temperature compensated to 25°C.',
                'is_locked' => 0,
                'submitted_at' => now()->subHours(5 - $idx),
                'created_at' => now()->subHours(5 - $idx),
                'updated_at' => now()->subHours(5 - $idx),
            ]);
        }

        // 4. Past Archived 2025 PT Programs (Seeded with Registrations & Observations)
        $pastDate = now()->subYear();

        // 2025 Program 1: Soil Analysis
        DB::table('pt_programs')->updateOrInsert(
            ['program_code' => 'PT-CHEM-2025-01'],
            [
                'program_name' => 'Physico-Chemical Analysis in Agricultural Soil',
                'discipline' => 'Chemical',
                'scheme_code' => 'SCH-SOIL-2025',
                'description' => 'Annual proficiency testing for Organic Carbon, Available Nitrogen, and Soil pH.',
                'program_fee' => 12500.00,
                'registration_start_date' => '2025-01-10',
                'registration_end_date' => '2025-02-10',
                'dispatch_date' => '2025-02-20',
                'submission_deadline' => '2025-03-20',
                'report_date' => '2025-04-05',
                'registration_status' => 'closed',
                'program_status' => 'completed',
                'created_by' => $admin->admin_id,
                'created_at' => $pastDate,
                'updated_at' => $pastDate,
            ]
        );
        $prog2025Id = DB::table('pt_programs')->where('program_code', 'PT-CHEM-2025-01')->value('program_id');

        DB::table('program_parameters')->where('program_id', $prog2025Id)->delete();
        DB::table('program_parameters')->insert([
            ['program_id' => $prog2025Id, 'parameter_name' => 'Organic Carbon', 'test_method' => 'Walkley-Black Method', 'unit' => '%', 'created_at' => $pastDate],
            ['program_id' => $prog2025Id, 'parameter_name' => 'Available Nitrogen', 'test_method' => 'Kjeldahl Method', 'unit' => 'kg/ha', 'created_at' => $pastDate]
        ]);
        $paramOc = DB::table('program_parameters')->where('program_id', $prog2025Id)->where('parameter_name', 'Organic Carbon')->value('parameter_id');

        // Seed 4 Labs for 2025 Soil Program
        $soilOcResults = ['0.75', '0.78', '0.72', '0.85'];
        foreach (array_slice($labIds, 0, 4) as $idx => $lId) {
            $regNum2025 = 'REG-2025-000' . ($idx + 1);
            DB::table('program_registrations')->updateOrInsert(
                ['registration_number' => $regNum2025],
                [
                    'program_id' => $prog2025Id,
                    'lab_id' => $lId,
                    'sample_quantity' => '1 Jar (200g)',
                    'shipping_address' => 'Industrial Area',
                    'billing_address' => 'Industrial Area',
                    'discount_applied' => 0.00,
                    'status' => 'confirmed',
                    'registered_at' => $pastDate->copy()->addDays(2),
                ]
            );
            $r2025Id = DB::table('program_registrations')->where('registration_number', $regNum2025)->value('registration_id');

            DB::table('payments')->updateOrInsert(
                ['registration_id' => $r2025Id],
                [
                    'amount' => 12500.00,
                    'discount_amount' => 0.00,
                    'final_amount' => 12500.00,
                    'payment_method' => 'Bank Transfer',
                    'transaction_id' => 'TXN2025' . ($idx + 1),
                    'payment_status' => 'success',
                    'paid_at' => $pastDate->copy()->addDays(2),
                    'created_at' => $pastDate->copy()->addDays(2),
                ]
            );

            $sCode2025 = 'PT-2025-00' . ($idx + 1);
            DB::table('samples')->updateOrInsert(
                ['sample_code' => $sCode2025],
                [
                    'program_id' => $prog2025Id,
                    'registration_id' => $r2025Id,
                    'batch_id' => $batch1Id,
                    'qr_code' => 'QR-' . $sCode2025,
                    'status' => 'dispatched',
                    'created_at' => $pastDate->copy()->addDays(10),
                ]
            );
            $s2025Id = DB::table('samples')->where('sample_code', $sCode2025)->value('sample_id');

            DB::table('dispatches')->updateOrInsert(
                ['sample_id' => $s2025Id],
                [
                    'dispatch_date' => $pastDate->copy()->addDays(12)->toDateString(),
                    'courier_name' => 'BlueDart Express',
                    'tracking_number' => 'BD-2025-00' . ($idx + 1),
                    'dispatched_by' => $admin->admin_id,
                    'notification_sent' => 1,
                    'notification_sent_at' => $pastDate->copy()->addDays(12),
                    'created_at' => $pastDate->copy()->addDays(12),
                ]
            );

            DB::table('observations')->insert([
                'sample_id' => $s2025Id,
                'registration_id' => $r2025Id,
                'lab_id' => $lId,
                'parameter_id' => $paramOc,
                'test_method' => 'Walkley-Black Method',
                'result_value' => $soilOcResults[$idx],
                'unit' => '%',
                'remarks' => 'Completed in 2025.',
                'is_locked' => 1,
                'submitted_at' => $pastDate->copy()->addDays(25),
                'created_at' => $pastDate->copy()->addDays(25),
                'updated_at' => $pastDate->copy()->addDays(25),
            ]);
        }

        // 2025 Program 2: Biological Testing
        DB::table('pt_programs')->updateOrInsert(
            ['program_code' => 'PT-BIO-2025-02'],
            [
                'program_name' => 'Microbiological Enumeration in Processed Food',
                'discipline' => 'Biological',
                'scheme_code' => 'SCH-BIO-2025',
                'description' => 'Proficiency testing for E. Coli and Total Plate Count in powdered milk matrix.',
                'program_fee' => 18000.00,
                'registration_start_date' => '2025-05-01',
                'registration_end_date' => '2025-06-01',
                'dispatch_date' => '2025-06-15',
                'submission_deadline' => '2025-07-15',
                'report_date' => '2025-08-01',
                'registration_status' => 'closed',
                'program_status' => 'completed',
                'created_by' => $admin->admin_id,
                'created_at' => $pastDate->copy()->addMonths(4),
                'updated_at' => $pastDate->copy()->addMonths(4),
            ]
        );
        $progBioId = DB::table('pt_programs')->where('program_code', 'PT-BIO-2025-02')->value('program_id');

        DB::table('program_parameters')->where('program_id', $progBioId)->delete();
        DB::table('program_parameters')->insert([
            ['program_id' => $progBioId, 'parameter_name' => 'Total Plate Count', 'test_method' => 'IS 5402', 'unit' => 'CFU/g', 'created_at' => $pastDate],
            ['program_id' => $progBioId, 'parameter_name' => 'E. Coli Count', 'test_method' => 'IS 5887 (Part 1)', 'unit' => 'CFU/g', 'created_at' => $pastDate]
        ]);
        $paramTpc = DB::table('program_parameters')->where('program_id', $progBioId)->where('parameter_name', 'Total Plate Count')->value('parameter_id');

        // Seed 3 Labs for Bio 2025
        $bioTpcResults = ['3500', '3400', '3650'];
        foreach (array_slice($labIds, 0, 3) as $idx => $lId) {
            $regBioNum = 'REG-BIO-2025-0' . ($idx + 1);
            DB::table('program_registrations')->updateOrInsert(
                ['registration_number' => $regBioNum],
                [
                    'program_id' => $progBioId,
                    'lab_id' => $lId,
                    'sample_quantity' => '1 Vial (10g)',
                    'shipping_address' => 'Bio Park',
                    'billing_address' => 'Bio Park',
                    'discount_applied' => 0.00,
                    'status' => 'confirmed',
                    'registered_at' => $pastDate->copy()->addMonths(4)->addDays(5),
                ]
            );
            $rBioId = DB::table('program_registrations')->where('registration_number', $regBioNum)->value('registration_id');

            DB::table('payments')->updateOrInsert(
                ['registration_id' => $rBioId],
                [
                    'amount' => 18000.00,
                    'discount_amount' => 0.00,
                    'final_amount' => 18000.00,
                    'payment_method' => 'NEFT',
                    'transaction_id' => 'BIO2025' . ($idx + 1),
                    'payment_status' => 'success',
                    'paid_at' => $pastDate->copy()->addMonths(4)->addDays(5),
                    'created_at' => $pastDate->copy()->addMonths(4)->addDays(5),
                ]
            );

            $sBioCode = 'PT-BIO-2025-0' . ($idx + 1);
            DB::table('samples')->updateOrInsert(
                ['sample_code' => $sBioCode],
                [
                    'program_id' => $progBioId,
                    'registration_id' => $rBioId,
                    'batch_id' => $batch1Id,
                    'qr_code' => 'QR-' . $sBioCode,
                    'status' => 'dispatched',
                    'created_at' => $pastDate->copy()->addMonths(4)->addDays(15),
                ]
            );
            $sBioId = DB::table('samples')->where('sample_code', $sBioCode)->value('sample_id');

            DB::table('dispatches')->updateOrInsert(
                ['sample_id' => $sBioId],
                [
                    'dispatch_date' => $pastDate->copy()->addMonths(4)->addDays(16)->toDateString(),
                    'courier_name' => 'DTDC Courier',
                    'tracking_number' => 'DT-BIO-00' . ($idx + 1),
                    'dispatched_by' => $admin->admin_id,
                    'notification_sent' => 1,
                    'notification_sent_at' => $pastDate->copy()->addMonths(4)->addDays(16),
                    'created_at' => $pastDate->copy()->addMonths(4)->addDays(16),
                ]
            );

            DB::table('observations')->insert([
                'sample_id' => $sBioId,
                'registration_id' => $rBioId,
                'lab_id' => $lId,
                'parameter_id' => $paramTpc,
                'test_method' => 'IS 5402',
                'result_value' => $bioTpcResults[$idx],
                'unit' => 'CFU/g',
                'remarks' => 'Bio evaluation completed.',
                'is_locked' => 1,
                'submitted_at' => $pastDate->copy()->addMonths(4)->addDays(25),
                'created_at' => $pastDate->copy()->addMonths(4)->addDays(25),
                'updated_at' => $pastDate->copy()->addMonths(4)->addDays(25),
            ]);
        }
    }
}
