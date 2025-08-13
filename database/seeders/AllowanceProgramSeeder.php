<?php

namespace Database\Seeders;

use App\Models\AllowanceProgram;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AllowanceProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $query = "INSERT INTO allowance_programs (name_en,name_bn,payment_cycle,is_marital,marital_status,is_active,is_age_limit,is_disable_class,created_at,updated_at,pmt_status,system_status) VALUES
	 ('Old Age Allowance Programme','Old Age Allowance Programme','Monthly',0,NULL,0,0,0,'2024-01-24 18:54:38','2024-01-24 18:54:38',1,1),
	 ('Widow Allowance Programme','Widow Allowance Programme','Quarterly',0,NULL,0,0,0,'2024-01-24 18:54:51','2024-01-24 18:54:51',1,1),
	 ('Allowance Programme for Disable People','Allowance Programme for Disable People','Half Yearly',0,NULL,0,0,0,'2024-01-24 18:55:04','2024-01-24 18:55:04',1,1),
	 ('Tea Workers Allowance Programme','Tea Workers Allowance Programme','Yearly',0,NULL,0,0,0,'2024-01-24 18:55:16','2024-01-24 18:55:16',1,1);";
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        AllowanceProgram::truncate();
        DB::statement($query);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
