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
        // Default Admin User
        AdminUser::updateOrCreate(
            ['username' => 'admin'],
            [
                'full_name' => 'System Administrator',
                'email' => 'admin@ptsoftware.com',
                'password_hash' => Hash::make('password123'),
                'status' => 'active'
            ]
        );

        // Sample Lab 1
        $lab1Id = DB::table('labs')->insertGetId([
            'laboratory_name' => 'Apex Analytical Services',
            'nabl_certificate_number' => 'TC-8912',
            'laboratory_type' => 'Commercial Laboratory',
            'gst_number' => '27AAACA1234A1Z5',
            'address' => 'Plot 42, Industrial Area Phase 2',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'country' => 'India',
            'pin_code' => '400083',
            'contact_person' => 'Dr. Rajesh Sharma',
            'designation' => 'Quality Manager',
            'email' => 'rajesh@apexlabs.com',
            'mobile_number' => '+91 9876543210',
            'username' => 'lab_apex',
            'password_hash' => Hash::make('password123'),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Sample Lab 2
        $lab2Id = DB::table('labs')->insertGetId([
            'laboratory_name' => 'QualiTest Research Lab',
            'nabl_certificate_number' => 'TC-5431',
            'laboratory_type' => 'Research Institute',
            'gst_number' => '07BBBCB5678B1Z2',
            'address' => 'Sector 18, Electronic City',
            'city' => 'Gurugram',
            'state' => 'Haryana',
            'country' => 'India',
            'pin_code' => '122015',
            'contact_person' => 'Sunita Patel',
            'designation' => 'Technical Director',
            'email' => 'sunita@qualitest.org',
            'mobile_number' => '+91 9812345678',
            'username' => 'lab_qualitest',
            'password_hash' => Hash::make('password123'),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Sample PT Program 1
        $admin = AdminUser::first();
        $prog1Id = DB::table('pt_programs')->insertGetId([
            'program_code' => 'PT-CHEM-2026-01',
            'program_name' => 'Chemical Analysis in Water Samples (ISO 17025)',
            'discipline' => 'Chemical',
            'scheme_code' => 'SCH-CHEM-01',
            'description' => 'Proficiency testing for pH, Conductivity, and Heavy Metals determination in drinking water.',
            'program_fee' => 15000.00,
            'registration_start_date' => now()->subDays(10),
            'registration_end_date' => now()->addDays(20),
            'dispatch_date' => now()->addDays(25),
            'submission_deadline' => now()->addDays(45),
            'report_date' => now()->addDays(60),
            'registration_status' => 'active',
            'program_status' => 'open',
            'created_by' => $admin ? $admin->admin_id : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Sample Program Parameters
        DB::table('program_parameters')->insert([
            [
                'program_id' => $prog1Id,
                'parameter_name' => 'pH Value',
                'test_method' => 'IS 3025 (Part 11)',
                'unit' => 'pH Units',
                'created_at' => now(),
            ],
            [
                'program_id' => $prog1Id,
                'parameter_name' => 'Electrical Conductivity',
                'test_method' => 'IS 3025 (Part 14)',
                'unit' => 'µS/cm',
                'created_at' => now(),
            ],
            [
                'program_id' => $prog1Id,
                'parameter_name' => 'Total Dissolved Solids (TDS)',
                'test_method' => 'IS 3025 (Part 16)',
                'unit' => 'mg/L',
                'created_at' => now(),
            ]
        ]);

        // Sample Program Registration & Payment for Lab 1
        $reg1Id = DB::table('program_registrations')->insertGetId([
            'registration_number' => 'REG-2026-0001',
            'program_id' => $prog1Id,
            'lab_id' => $lab1Id,
            'sample_quantity' => '2 Bottles (500ml each)',
            'shipping_address' => 'Plot 42, Industrial Area Phase 2, Mumbai',
            'billing_address' => 'Plot 42, Industrial Area Phase 2, Mumbai',
            'discount_applied' => 0.00,
            'status' => 'confirmed',
            'registered_at' => now(),
        ]);

        DB::table('payments')->insert([
            'registration_id' => $reg1Id,
            'amount' => 15000.00,
            'discount_amount' => 0.00,
            'final_amount' => 15000.00,
            'payment_method' => 'NEFT / Bank Transfer',
            'transaction_id' => 'TXN987654321',
            'payment_status' => 'success',
            'paid_at' => now(),
            'created_at' => now(),
        ]);
    }
}
