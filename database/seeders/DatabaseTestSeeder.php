<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lab;
use App\Models\PtProgram;
use App\Models\ProgramParameter;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseTestSeeder extends Seeder
{
    public function run()
    {
        // 1. Ensure we have an admin user to assign created_by
        $admin = AdminUser::first();
        if (!$admin) {
            $admin = AdminUser::create([
                'username'      => 'admin',
                'password_hash' => Hash::make('admin123'),
                'full_name'     => 'System Admin',
                'email'         => 'admin@example.com',
                'role'          => 'admin',
                'status'        => 'active',
                'created_at'    => now()
            ]);
        }

        // 2. Seed 4 Laboratories
        $labsData = [
            [
                'laboratory_name'         => 'Alpha Analytics Research Lab',
                'nabl_certificate_number' => 'TC-8501',
                'laboratory_type'         => 'testing',
                'gst_number'              => '27AAAAA1111A1Z1',
                'address'                 => '101 Science Park, Sector 4',
                'city'                    => 'Mumbai',
                'state'                   => 'Maharashtra',
                'country'                 => 'India',
                'pin_code'                => '400051',
                'contact_person'          => 'Dr. Amit Patel',
                'designation'             => 'Director',
                'email'                   => 'lab1@example.com',
                'mobile_number'           => '9876543210',
                'username'                => 'lab1@example.com',
                'password_hash'           => Hash::make('password123'),
                'status'                  => 'active'
            ],
            [
                'laboratory_name'         => 'Beta Environmental Testing Services',
                'nabl_certificate_number' => 'TC-8502',
                'laboratory_type'         => 'testing',
                'gst_number'              => '27BBBBB2222B2Z2',
                'address'                 => '202 Eco Towers, GIDC',
                'city'                    => 'Ahmedabad',
                'state'                   => 'Gujarat',
                'country'                 => 'India',
                'pin_code'                => '380015',
                'contact_person'          => 'Dr. Rajesh Shah',
                'designation'             => 'Technical Manager',
                'email'                   => 'lab2@example.com',
                'mobile_number'           => '9876543211',
                'username'                => 'lab2@example.com',
                'password_hash'           => Hash::make('password123'),
                'status'                  => 'active'
            ],
            [
                'laboratory_name'         => 'Gamma Materials & Metallurgy Lab',
                'nabl_certificate_number' => 'TC-8503',
                'laboratory_type'         => 'calibration',
                'gst_number'              => '27CCCCC3333C3Z3',
                'address'                 => '303 Industrial Estate, Phase 2',
                'city'                    => 'Pune',
                'state'                   => 'Maharashtra',
                'country'                 => 'India',
                'pin_code'                => '411018',
                'contact_person'          => 'Sanjay Mehta',
                'designation'             => 'Quality Manager',
                'email'                   => 'lab3@example.com',
                'mobile_number'           => '9876543212',
                'username'                => 'lab3@example.com',
                'password_hash'           => Hash::make('password123'),
                'status'                  => 'active'
            ],
            [
                'laboratory_name'         => 'Delta Food Safety & Bio Labs',
                'nabl_certificate_number' => 'TC-8504',
                'laboratory_type'         => 'testing',
                'gst_number'              => '27DDDDD4444D4Z4',
                'address'                 => '404 Agri Biotech park',
                'city'                    => 'Bengaluru',
                'state'                   => 'Karnataka',
                'country'                 => 'India',
                'pin_code'                => '560001',
                'contact_person'          => 'Priya Nair',
                'designation'             => 'Senior Microbiologist',
                'email'                   => 'lab4@example.com',
                'mobile_number'           => '9876543213',
                'username'                => 'lab4@example.com',
                'password_hash'           => Hash::make('password123'),
                'status'                  => 'active'
            ]
        ];

        foreach ($labsData as $labData) {
            Lab::updateOrCreate(['email' => $labData['email']], $labData);
        }

        // 3. Seed 4 PT Programs (with parameter list)
        $programsData = [
            [
                'info' => [
                    'program_code'            => 'PT-2026-SOIL',
                    'program_name'            => 'Agricultural Soil Chemical Analysis',
                    'discipline'              => 'chemical',
                    'scheme_code'             => 'SCH-SOIL-01',
                    'description'             => 'Proficiency testing scheme for determination of soil nutrients, Organic Carbon, and pH levels.',
                    'program_fee'             => 5500.00,
                    'registration_start_date' => Carbon::now()->subMonths(1)->toDateString(),
                    'registration_end_date'   => Carbon::now()->addMonths(6)->toDateString(),
                    'dispatch_date'           => Carbon::now()->addMonths(7)->toDateString(),
                    'submission_deadline'     => Carbon::now()->addMonths(8)->toDateString(),
                    'report_date'             => Carbon::now()->addMonths(9)->toDateString(),
                    'registration_status'     => 'active',
                    'program_status'          => 'open',
                    'created_by'              => $admin->admin_id
                ],
                'parameters' => [
                    ['parameter_name' => 'Organic Carbon', 'test_method' => 'Walkley-Black Method', 'unit' => '%'],
                    ['parameter_name' => 'Available Nitrogen', 'test_method' => 'Subbiah & Asija Method', 'unit' => 'mg/kg'],
                    ['parameter_name' => 'Soil pH', 'test_method' => '1:2.5 Soil-Water Suspension', 'unit' => 'pH Units']
                ]
            ],
            [
                'info' => [
                    'program_code'            => 'PT-2026-WATER',
                    'program_name'            => 'Potable Drinking Water Analysis',
                    'discipline'              => 'chemical',
                    'scheme_code'             => 'SCH-WAT-02',
                    'description'             => 'Drinking water proficiency testing scheme for heavy metals and basic potability parameters.',
                    'program_fee'             => 6000.00,
                    'registration_start_date' => Carbon::now()->subMonths(1)->toDateString(),
                    'registration_end_date'   => Carbon::now()->addMonths(6)->toDateString(),
                    'dispatch_date'           => Carbon::now()->addMonths(7)->toDateString(),
                    'submission_deadline'     => Carbon::now()->addMonths(8)->toDateString(),
                    'report_date'             => Carbon::now()->addMonths(9)->toDateString(),
                    'registration_status'     => 'active',
                    'program_status'          => 'open',
                    'created_by'              => $admin->admin_id
                ],
                'parameters' => [
                    ['parameter_name' => 'Fluoride (as F)', 'test_method' => 'SPADNS Colorimetric', 'unit' => 'mg/L'],
                    ['parameter_name' => 'Lead (as Pb)', 'test_method' => 'ICP-MS / AAS', 'unit' => 'µg/L'],
                    ['parameter_name' => 'Turbidity', 'test_method' => 'Nephelometric Method', 'unit' => 'NTU']
                ]
            ],
            [
                'info' => [
                    'program_code'            => 'PT-2026-CHEM',
                    'program_name'            => 'Industrial Chemical Purity & Moisture Analysis',
                    'discipline'              => 'chemical',
                    'scheme_code'             => 'SCH-CHEM-03',
                    'description'             => 'Assay purity determination and moisture analysis in industrial chemical compounds.',
                    'program_fee'             => 7500.00,
                    'registration_start_date' => Carbon::now()->subMonths(1)->toDateString(),
                    'registration_end_date'   => Carbon::now()->addMonths(6)->toDateString(),
                    'dispatch_date'           => Carbon::now()->addMonths(7)->toDateString(),
                    'submission_deadline'     => Carbon::now()->addMonths(8)->toDateString(),
                    'report_date'             => Carbon::now()->addMonths(9)->toDateString(),
                    'registration_status'     => 'active',
                    'program_status'          => 'open',
                    'created_by'              => $admin->admin_id
                ],
                'parameters' => [
                    ['parameter_name' => 'Purity Assay', 'test_method' => 'HPLC Quantitative Analysis', 'unit' => '%'],
                    ['parameter_name' => 'Moisture Content', 'test_method' => 'Karl Fischer Titration', 'unit' => '%']
                ]
            ],
            [
                'info' => [
                    'program_code'            => 'PT-2026-FOOD',
                    'program_name'            => 'Food Safety Microbiological Scheme',
                    'discipline'              => 'biological',
                    'scheme_code'             => 'SCH-MICRO-04',
                    'description'             => 'Microbiological identification and enumeration of common food pathogens.',
                    'program_fee'             => 8000.00,
                    'registration_start_date' => Carbon::now()->subMonths(1)->toDateString(),
                    'registration_end_date'   => Carbon::now()->addMonths(6)->toDateString(),
                    'dispatch_date'           => Carbon::now()->addMonths(7)->toDateString(),
                    'submission_deadline'     => Carbon::now()->addMonths(8)->toDateString(),
                    'report_date'             => Carbon::now()->addMonths(9)->toDateString(),
                    'registration_status'     => 'active',
                    'program_status'          => 'open',
                    'created_by'              => $admin->admin_id
                ],
                'parameters' => [
                    ['parameter_name' => 'Escherichia coli Count', 'test_method' => 'ISO 16649 TBX Agar', 'unit' => 'CFU/g'],
                    ['parameter_name' => 'Salmonella spp.', 'test_method' => 'ISO 6579 Detection', 'unit' => 'Presence/Absence']
                ]
            ]
        ];

        foreach ($programsData as $progData) {
            $program = PtProgram::updateOrCreate(
                ['program_code' => $progData['info']['program_code']],
                $progData['info']
            );

            // Seed parameters
            foreach ($progData['parameters'] as $param) {
                ProgramParameter::updateOrCreate(
                    [
                        'program_id'     => $program->program_id,
                        'parameter_name' => $param['parameter_name']
                    ],
                    [
                        'test_method' => $param['test_method'],
                        'unit'        => $param['unit']
                    ]
                );
            }
        }
    }
}
