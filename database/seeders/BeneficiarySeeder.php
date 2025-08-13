<?php

namespace Database\Seeders;

use App\Models\Beneficiary;
use App\Models\Payroll;
use App\Models\PayrollDetail;
use Illuminate\Database\Seeder;

class BeneficiarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Beneficiary::factory(50)->create();
    }
}
