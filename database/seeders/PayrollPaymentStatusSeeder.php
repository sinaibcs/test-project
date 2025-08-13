<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PayrollPaymentStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('payroll_payment_statuses')->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $statuses = [
            ['id' => 1, 'name_en' => 'Payroll Pending', 'name_bn' => 'পেরোল অপেক্ষমান'],
            ['id' => 2, 'name_en' => 'Payroll Approved', 'name_bn' => 'পেরোল অনুমোদিত'],
            ['id' => 3, 'name_en' => 'Payroll Rejected', 'name_bn' => 'পেরোল প্রত্যাখ্যাত'],
            ['id' => 4, 'name_en' => 'Payment Cycle Pending', 'name_bn' => 'পেমেন্ট চক্র অপেক্ষমান'],
            ['id' => 5, 'name_en' => 'Payment Cycle Initiated', 'name_bn' => 'পেমেন্ট উদ্যোগ নেওয়া হয়েছে'],
            ['id' => 6, 'name_en' => 'Payment Completed', 'name_bn' => 'পেমেন্ট সম্পন্ন হয়েছে'],
            ['id' => 7, 'name_en' => 'Payment Failed', 'name_bn' => 'পেমেন্ট ব্যর্থ হয়েছে'],
            ['id' => 8, 'name_en' => 'Information Updated', 'name_bn' => 'তথ্য সংশোধিত'],
            ['id' => 9, 'name_en' => 'Cycle Rejected', 'name_bn' => 'সাইকেল বাতিল করা হয়েছে'],
            ['id' => 10, 'name_en' => 'Deleted', 'name_bn' => 'মুছে ফেলা হয়েছে'],
            ['id' => 11, 'name_en' => 'Edited', 'name_bn' => 'সম্পাদনা করা হয়েছে'],
        ];

        DB::table('payroll_payment_statuses')->insert($statuses);
    }
}