<?php

namespace Database\Seeders;

use App\Models\Lookup;
use DB;
use Illuminate\Database\Seeder;

class LookUpSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lookups = [
            ['id' => 1, 'type' => 1, 'value_en' => 'District Pouroshava', 'value_bn' => 'জেলা পৌরসভা', 'version' => 1, 'default' => 1],
            ['id' => 2, 'type' => 1, 'value_en' => 'Upazila', 'value_bn' => 'উপজেলা', 'version' => 2, 'default' => 1],
            ['id' => 3, 'type' => 1, 'value_en' => 'City Corporation', 'value_bn' => 'সিটি কর্পোরেশন', 'version' => 3, 'default' => 1],
            ['id' => 4, 'type' => 3, 'value_en' => 'Ministry', 'value_bn' => 'মন্ত্রণালয়', 'default' => 1],
            ['id' => 5, 'type' => 3, 'value_en' => 'Head Office', 'value_bn' => 'হেড অফিস', 'default' => 1],
            ['id' => 6, 'type' => 3, 'value_en' => 'Division', 'value_bn' => 'বিভাগ', 'default' => 1],
            ['id' => 7, 'type' => 3, 'value_en' => 'District', 'value_bn' => 'জেলা', 'default' => 1],
            ['id' => 8, 'type' => 3, 'value_en' => 'Upazila', 'value_bn' => 'উপজেলা', 'default' => 1],
            ['id' => 9, 'type' => 3, 'value_en' => 'UCD (City Corporation)', 'value_bn' => 'ইউসিডি (সিটি কর্পোরেশন)', 'default' => 1],
            ['id' => 10, 'type' => 3, 'value_en' => 'UCD (Upazila)', 'value_bn' => 'ইউসিডি (উপজেলা)', 'default' => 1],
            ['id' => 11, 'type' => 3, 'value_en' => 'Circle Social Service', 'value_bn' => 'সার্কেল সমাজসেবা', 'default' => 1],
            ['id' => 12, 'type' => 17, 'value_en' => 'Union Committee', 'value_bn' => 'ইউনিয়ন কমিটি', 'default' => 1],
            ['id' => 13, 'type' => 17, 'value_en' => 'Ward Committee', 'value_bn' => 'ওয়ার্ড কমিটি', 'default' => 1],
            ['id' => 14, 'type' => 17, 'value_en' => 'Upazila Committee', 'value_bn' => 'উপজেলা কমিটি', 'default' => 1],
            ['id' => 15, 'type' => 17, 'value_en' => 'City Corporation Committee', 'value_bn' => 'সিটি কর্পোরেশন কমিটি', 'default' => 1],
            ['id' => 16, 'type' => 17, 'value_en' => 'District Paurashava Committee', 'value_bn' => 'জেলা পৌরসভা কমিটি', 'default' => 1],
            ['id' => 17, 'type' => 17, 'value_en' => 'District Committee', 'value_bn' => 'জেলা কমিটি', 'default' => 1],
            ['id' => 18, 'type' => 17, 'value_en' => 'Coordination and Monitoring Committee', 'value_bn' => 'সমন্বয় ও মনিটরিং কমিটি', 'default' => 1],
            ['id' => 19, 'type' => 17, 'value_en' => 'National Steering Committee', 'value_bn' => 'জাতীয় স্টিয়ারিং কমিটি', 'default' => 1],
            ['id' => 20, 'type' => 18, 'value_en' => 'President', 'value_bn' => 'রাষ্ট্রপতি', 'default' => 1],
            ['id' => 21, 'type' => 18, 'value_en' => 'Vice President', 'value_bn' => 'সহ-সভাপতি', 'default' => 1],
            ['id' => 22, 'type' => 18, 'value_en' => 'Member', 'value_bn' => 'সদস্য', 'default' => 1],

            ///GENDER Seeder
            ['id' => 23, 'type' => 2, 'value_en' => 'Male', 'value_bn' => 'পুরুষ', 'default' => 1],
            ['id' => 24, 'type' => 2, 'value_en' => 'Female', 'value_bn' => 'মহিলা', 'default' => 1],
            ['id' => 95, 'type' => 2, 'value_en' => 'Transgender', 'value_bn' => 'হিজড়া', 'default' => 1],

            ///CLASS Seeder
            ['id' => 25, 'type' => 20, 'value_en' => 'Primary level (Class One,Class Two,Class Three,Class Four ,Class Five)', 'value_bn' => 'প্রাথমিক স্তর (প্রথম শ্রেণী,দ্বিতীয় শ্রেণী,তৃতীয় শ্রেণী,চতুর্থ শ্রেণী,পঞ্চম শ্রেণী)', 'default' => 1],
            ['id' => 26, 'type' => 20, 'value_en' => 'Secondary level (Class Six,Class Seven,Class Eight,Class Nine ,Class Ten)', 'value_bn' => 'মাধ্যমিক স্তর (ষষ্ঠ শ্রেণী,সপ্তম শ্রেণী,অষ্টম শ্রেণী,নবম শ্রেণী,দশম শ্রেণী)', 'default' => 1],
            ['id' => 27, 'type' => 20, 'value_en' => 'Heigher Secondary level (Class XII,Class XII)', 'value_bn' => 'উচ্চ মাধ্যমিক স্তর (একাদশ শ্রেণী ,দ্বাদশ শ্রেণী)', 'default' => 1],

            // ['id' => 28, 'type' => 20, 'value_en' => 'Fourth Layer', 'value_bn' => 'চতুর্থ স্তর', 'default' => 1],
            // ['id' => 29, 'type' => 20, 'value_en' => 'Fifth Layer', 'value_bn' => 'পঞ্চম স্তর', 'default' => 1],
            // ['id' => 30, 'type' => 20, 'value_en' => 'Sixth Layer', 'value_bn' => 'ষষ্ঠ স্তর', 'default' => 1],
            // ['id' => 31, 'type' => 20, 'value_en' => 'Seventh Layer', 'value_bn' => 'সপ্তম স্তর', 'default' => 1],
            // ['id' => 32, 'type' => 20, 'value_en' => 'Eighth Layer', 'value_bn' => 'অষ্টম স্তর', 'default' => 1],
            // ['id' => 33, 'type' => 20, 'value_en' => 'Ninth Layer', 'value_bn' => 'নবম স্তর', 'default' => 1],
            // ['id' => 34, 'type' => 20, 'value_en' => 'Tenth Layer', 'value_bn' => 'দশম স্তর', 'default' => 1],
            //office Type
            ['id' => 35, 'type' => 3, 'value_en' => 'UCD (Dist Paurashava)', 'value_bn' => 'ইউসিডি (জেলা পৌরসভা)', 'default' => 1],

            // Beneficiary replace reasons
            ['id' => 36, 'type' => 21, 'value_en' => 'Death', 'value_bn' => 'মৃত্যু', 'default' => 1],
            ['id' => 37, 'type' => 21, 'value_en' => 'Program Switch', 'value_bn' => 'প্রোগ্রাম পরিবর্তন', 'default' => 1],
            ['id' => 38, 'type' => 21, 'value_en' => 'Missing', 'value_bn' => 'অনুপস্থিত', 'default' => 1],
            ['id' => 155, 'type' => 22, 'value_en' => 'Remarriage', 'value_bn' => 'পুনর্বিবাহ', 'default' => 1],

            // Beneficiary exit reasons
            ['id' => 39, 'type' => 22, 'value_en' => 'Death', 'value_bn' => 'মৃত্যু', 'default' => 1],
            ['id' => 40, 'type' => 22, 'value_en' => 'Financially Independent', 'value_bn' => 'আর্থিকভাবে স্বচ্ছল', 'default' => 1],
            ['id' => 41, 'type' => 22, 'value_en' => 'Others', 'value_bn' => 'অন্যান্য', 'default' => 1],
            ['id' => 156, 'type' => 22, 'value_en' => 'Remarriage', 'value_bn' => 'পুনর্বিবাহ', 'default' => 1],

            ['id' => 42, 'type' => 20, 'value_en' => 'Others', 'value_bn' => 'অন্যান্য', 'default' => 1],
            ['id' => 43, 'type' => 18, 'value_en' => 'Secretary', 'value_bn' => 'সচিব', 'default' => 1],

            // Calculation Type
            ['id' => 44, 'type' => 23, 'value_en' => 'Percentage of Amount', 'value_bn' => 'শতকরা পরিমাণ অনুসারে', 'keyword' => 'PERCENTAGE_OF_AMOUNT', 'default' => 1],
            ['id' => 45, 'type' => 23, 'value_en' => 'Fixed Amount', 'value_bn' => 'নির্দিষ্ট পরিমাণ অনুসারে', 'keyword' => 'FIXED_AMOUNT', 'default' => 1],
            ['id' => 46, 'type' => 23, 'value_en' => 'Percentage of Beneficiary', 'value_bn' => 'উপকারভোগীর শতাংশ অনুসারে', 'keyword' => 'PERCENTAGE_OF_BENEFICIARY', 'default' => 1],
            ['id' => 47, 'type' => 23, 'value_en' => 'Fixed Beneficiary', 'value_bn' => 'স্থায়ী উপকারভোগী অনুসারে', 'keyword' => 'FIXED_BENEFICIARY', 'default' => 1],
            ['id' => 48, 'type' => 23, 'value_en' => 'By Application', 'value_bn' => 'আবেদন অনুসারে', 'keyword' => 'BY_APPLICATION', 'default' => 1],
            ['id' => 49, 'type' => 23, 'value_en' => 'By Poverty Score', 'value_bn' => 'দারিদ্র্য সূচক অনুসারে', 'keyword' => 'BY_POVERTY_SCORE', 'default' => 1],
            ['id' => 50, 'type' => 23, 'value_en' => 'By Population', 'value_bn' => 'জনসংখ্যা অনুসারে', 'keyword' => 'BY_POPULATION', 'default' => 1],
            ['id' => 85, 'type' => 23, 'value_en' => 'By Application Percentage', 'value_bn' => 'আবেদন শতাংশ অনুসারে', 'keyword' => 'BY_APPLICATION_PERCENTAGE', 'default' => 1],
            ['id' => 86, 'type' => 23, 'value_en' => 'By Population Percentage', 'value_bn' => 'জনসংখ্যা শতাংশ অনুসারে', 'keyword' => 'BY_POPULATION_PERCENTAGE', 'default' => 1],
            ['id' => 87, 'type' => 23, 'value_en' => 'By Poverty Score Percentage', 'value_bn' => 'দারিদ্র্য সূচক শতাংশ অনুসারে', 'keyword' => 'BY_POVERTY_SCORE_PERCENTAGE', 'default' => 1],

            //Trainer Designation
            ['id' => 51, 'type' => 24, 'value_en' => 'Senior trainer', 'value_bn' => 'ঊর্ধ্বতন প্রশিক্ষক', 'default' => 1],
            ['id' => 52, 'type' => 24, 'value_en' => 'Trainer', 'value_bn' => 'প্রশিক্ষক', 'default' => 1],

            // Grievance Solution type
            ['id' => 53, 'type' => 25, 'value_en' => 'Allegation have no prove or Truth', 'value_bn' => 'অভিযোগের কোনো প্রমাণ বা সত্যতা নেই', 'default' => 1],
            ['id' => 54, 'type' => 25, 'value_en' => 'Dispatch to relative office', 'value_bn' => 'আত্মীয় অফিসে প্রেরণ', 'default' => 1],
            ['id' => 55, 'type' => 25, 'value_en' => 'Solution Type -3', 'value_bn' => 'সমাধানের ধরন-৩', 'default' => 1],
            ['id' => 56, 'type' => 25, 'value_en' => 'Solution Type -4', 'value_bn' => 'সমাধানের ধরন-৪', 'default' => 1],
            ['id' => 57, 'type' => 25, 'value_en' => 'Solution Type -5', 'value_bn' => 'সমাধানের ধরন-৫', 'default' => 1],
            ['id' => 58, 'type' => 25, 'value_en' => 'Solution Type -6', 'value_bn' => 'সমাধানের ধরন-৬', 'default' => 1],
            //Training Circular Type
            ['id' => 59, 'type' => 26, 'value_en' => 'Open', 'value_bn' => 'উন্মুক্ত', 'default' => 1],
            ['id' => 60, 'type' => 26, 'value_en' => 'Selected', 'value_bn' => 'বাছাইকৃত', 'default' => 1],

            //Training Type
            ['id' => 61, 'type' => 27, 'value_en' => 'Online', 'value_bn' => 'অনলাইন', 'default' => 1],
            ['id' => 62, 'type' => 27, 'value_en' => 'Physical', 'value_bn' => 'ফিজিক্যাল', 'default' => 1],

            //Training Circular Status
            ['id' => 63, 'type' => 28, 'value_en' => 'Active', 'value_bn' => 'সক্রিয়', 'default' => 1],
            ['id' => 64, 'type' => 28, 'value_en' => 'Inactive', 'value_bn' => 'নিষ্ক্রিয়', 'default' => 1],
            ['id' => 65, 'type' => 28, 'value_en' => 'In-Progress', 'value_bn' => 'চলমান', 'default' => 1],
            ['id' => 66, 'type' => 28, 'value_en' => 'Completed', 'value_bn' => 'সম্পন্ন', 'default' => 1],

            //Training Module
            ['id' => 67, 'type' => 29, 'value_en' => 'System Configuration', 'value_bn' => 'সিস্টেম কনফিগারেশন', 'default' => 1],
            ['id' => 68, 'type' => 29, 'value_en' => 'Budget & Allotment', 'value_bn' => 'বাজেট ও বরাদ্দ', 'default' => 1],
            ['id' => 69, 'type' => 29, 'value_en' => 'Application & Selection', 'value_bn' => 'আবেদন ও নির্বাচন ব্যবস্থাপনা', 'default' => 1],
            ['id' => 70, 'type' => 29, 'value_en' => 'Beneficiary Management', 'value_bn' => 'উপকারভোগী ব্যবস্থাপনা', 'default' => 1],
            ['id' => 71, 'type' => 29, 'value_en' => 'Payroll Management', 'value_bn' => 'বেতন ব্যবস্থাপনা', 'default' => 1],
            ['id' => 72, 'type' => 29, 'value_en' => 'Emergency Payment', 'value_bn' => 'জরুরী পেমেন্ট', 'default' => 1],
            ['id' => 73, 'type' => 29, 'value_en' => 'Grievance Management', 'value_bn' => 'অভিযোগ ব্যবস্থাপনা', 'default' => 1],
            ['id' => 74, 'type' => 29, 'value_en' => 'M&E Reporting', 'value_bn' => 'এমএন্ডই এবং রিপোর্টিং', 'default' => 1],
            ['id' => 75, 'type' => 29, 'value_en' => 'API Manager', 'value_bn' => 'এপিআই ম্যানেজার', 'default' => 1],
            ['id' => 76, 'type' => 29, 'value_en' => 'System Audit', 'value_bn' => 'সিস্টেম অডিট', 'default' => 1],

            //Training Participant Organization
            ['id' => 77, 'type' => 30, 'value_en' => 'Ministry user', 'value_bn' => 'মিনিস্ট্রি ইউজার', 'default' => 1],
            ['id' => 78, 'type' => 30, 'value_en' => 'CTM', 'value_bn' => 'সিটিমএম', 'default' => 1],
            ['id' => 79, 'type' => 30, 'value_en' => 'USSO', 'value_bn' => 'ইউএসএসও', 'default' => 1],
            ['id' => 80, 'type' => 30, 'value_en' => 'UCD', 'value_bn' => 'ইউসিডি', 'default' => 1],

            //Training program status
            ['id' => 81, 'type' => 31, 'value_en' => 'Pending', 'value_bn' => 'বিচারাধীন', 'default' => 1],
            ['id' => 82, 'type' => 31, 'value_en' => 'In Progress', 'value_bn' => 'চলমান', 'default' => 1],
            ['id' => 83, 'type' => 31, 'value_en' => 'Completed', 'value_bn' => 'সম্পন্ন', 'default' => 1],
            ['id' => 84, 'type' => 31, 'value_en' => 'Postponed', 'value_bn' => 'স্থগিত', 'default' => 1],

            //Educational Status
            ['id' => 88, 'type' => 8, 'value_en' => 'Illiterate', 'value_bn' => 'নিরক্ষর', 'default' => 1],
            ['id' => 89, 'type' => 8, 'value_en' => 'JSC', 'value_bn' => 'জেএসসি', 'default' => 1],
            ['id' => 90, 'type' => 8, 'value_en' => 'SSC', 'value_bn' => 'এসএসসি', 'default' => 1],
            ['id' => 91, 'type' => 8, 'value_en' => 'HSC', 'value_bn' => 'এইচএসসি', 'default' => 1],
            ['id' => 92, 'type' => 8, 'value_en' => 'Graduate', 'value_bn' => 'গ্রাজুয়েট', 'default' => 1],
            ['id' => 93, 'type' => 8, 'value_en' => 'Post Graduate', 'value_bn' => 'পোস্ট গ্রাজুয়েট', 'default' => 1],
            ['id' => 94, 'type' => 8, 'value_en' => 'Others', 'value_bn' => 'অন্যান্য', 'default' => 1],

            // Religion Status
            ['id' => 96, 'type' => 9, 'value_en' => 'Islam', 'value_bn' => 'ইসলাম', 'default' => 1],
            ['id' => 97, 'type' => 9, 'value_en' => 'Hindu', 'value_bn' => 'হিন্দু', 'default' => 1],
            ['id' => 98, 'type' => 9, 'value_en' => 'Buddhist', 'value_bn' => 'বৌদ্ধ', 'default' => 1],
            ['id' => 99, 'type' => 9, 'value_en' => 'Christian', 'value_bn' => 'খ্রিষ্টান', 'default' => 1],
            ['id' => 100, 'type' => 9, 'value_en' => 'Others', 'value_bn' => 'অন্যান্য', 'default' => 1],

            // Maritial Status
            ['id' => 101, 'type' => 32, 'value_en' => 'Married', 'value_bn' => 'বিবাহিত', 'default' => 1],
            ['id' => 102, 'type' => 32, 'value_en' => 'Unmarried', 'value_bn' => 'অবিবাহিত', 'default' => 1],
            ['id' => 103, 'type' => 32, 'value_en' => 'Widower', 'value_bn' => 'বিধবা', 'default' => 1],
            ['id' => 104, 'type' => 32, 'value_en' => 'Others', 'value_bn' => 'অন্যান্য', 'default' => 1],

            // Nationality Status
            ['id' => 105, 'type' => 33, 'value_en' => 'Bangladeshi', 'value_bn' => 'বাংলাদেশী', 'default' => 1],

            // Profession Status
            ['id' => 106, 'type' => 34, 'value_en' => 'Laborer', 'value_bn' => 'শ্রমজীবী', 'default' => 1],
            ['id' => 107, 'type' => 34, 'value_en' => 'Day laborer', 'value_bn' => 'দিনমজুর', 'default' => 1],
            ['id' => 108, 'type' => 34, 'value_en' => 'Farming', 'value_bn' => 'কৃষিকাজ', 'default' => 1],
            ['id' => 109, 'type' => 34, 'value_en' => 'Business', 'value_bn' => 'ব্যবসা', 'default' => 1],
            ['id' => 110, 'type' => 34, 'value_en' => 'Job', 'value_bn' => 'চাকুরি', 'default' => 1],
            ['id' => 111, 'type' => 34, 'value_en' => 'Student', 'value_bn' => 'শিক্ষার্থী', 'default' => 1],
            ['id' => 112, 'type' => 34, 'value_en' => 'Unemployed', 'value_bn' => 'কর্মহীন', 'default' => 1],
            ['id' => 113, 'type' => 34, 'value_en' => 'Others', 'value_bn' => 'অন্যান্য', 'default' => 1],

            // Class Status
//            ['id' => 110, 'type' => 20, 'value_en' => 'SSC', 'value_bn' => 'এসএসসি', 'default' => 1],
//            ['id' => 112, 'type' => 20, 'value_en' => 'Post graduate', 'value_bn' => 'পোষ্ট গ্রাজুয়েট', 'default' => 1],
//            ['id' => 113, 'type' => 20, 'value_en' => 'Graduate/Diploma', 'value_bn' => 'গ্রাজুয়েট/ডিপ্লোমা', 'default' => 1],

            // Month
            ['id' => 114, 'type' => 39, 'value_en' => 'January', 'value_bn' => 'জানুয়ারি', 'default' => 1],
            ['id' => 115, 'type' => 39, 'value_en' => 'February', 'value_bn' => 'ফেব্রুয়ারি', 'default' => 1],
            ['id' => 116, 'type' => 39, 'value_en' => 'March', 'value_bn' => 'মার্চ', 'default' => 1],
            ['id' => 117, 'type' => 39, 'value_en' => 'April', 'value_bn' => 'এপ্রিল', 'default' => 1],
            ['id' => 118, 'type' => 39, 'value_en' => 'May', 'value_bn' => 'মে', 'default' => 1],
            ['id' => 119, 'type' => 39, 'value_en' => 'June', 'value_bn' => 'জুন', 'default' => 1],
            ['id' => 120, 'type' => 39, 'value_en' => 'July', 'value_bn' => 'জুলাই', 'default' => 1],
            ['id' => 121, 'type' => 39, 'value_en' => 'August', 'value_bn' => 'আগস্ট', 'default' => 1],
            ['id' => 122, 'type' => 39, 'value_en' => 'September', 'value_bn' => 'সেপ্টেম্বর', 'default' => 1],
            ['id' => 123, 'type' => 39, 'value_en' => 'October', 'value_bn' => 'অক্টোবর', 'default' => 1],
            ['id' => 124, 'type' => 39, 'value_en' => 'November', 'value_bn' => 'নভেম্বর', 'default' => 1],
            ['id' => 125, 'type' => 39, 'value_en' => 'December', 'value_bn' => 'ডিসেম্বর', 'default' => 1],

            // Health Status
            ['id' => 126, 'type' => 4, 'value_en' => 'Crippled', 'value_bn' => 'পঙ্গু', 'default' => 1],
            ['id' => 127, 'type' => 4, 'value_en' => 'Permanent loss of eye sight', 'value_bn' => 'স্থায়ীভাবে চোখের দৃষ্টি ক্ষতি/লোপ', 'default' => 1],
            ['id' => 128, 'type' => 4, 'value_en' => 'Permanent hearing loss/loss of ear', 'value_bn' => 'স্থায়ীভাবে কানের শ্রবন শক্তি ক্ষতি/লোপ', 'default' => 1],
            ['id' => 129, 'type' => 4, 'value_en' => 'Damage to any organ or gland', 'value_bn' => 'যে কোন অঙ্গ বা গ্রন্থির ক্ষতি', 'default' => 1],
            ['id' => 130, 'type' => 4, 'value_en' => 'Decreased function of any organ or gland', 'value_bn' => 'যে কোন অঙ্গ বা গ্রন্থির কর্মশক্তি হ্রাস', 'default' => 1],
            ['id' => 131, 'type' => 4, 'value_en' => 'Permanent damage to the head or face', 'value_bn' => 'মাথা বা মুখমন্ডলের স্থায়ী ক্ষতি', 'default' => 1],
            ['id' => 132, 'type' => 4, 'value_en' => 'Bone or tooth fracture', 'value_bn' => 'হাড় বা দন্ত ভঙ্গ', 'default' => 1],
            ['id' => 133, 'type' => 4, 'value_en' => 'Traumatized', 'value_bn' => 'মানসিক ট্রমায় আক্রান্ত', 'default' => 1],
            ['id' => 134, 'type' => 4, 'value_en' => 'Any impairment that causes physical disability', 'value_bn' => 'যেকোনো অঙ্গহানি  যা শারীরিক অক্ষমতার কারণ', 'default' => 1],

            // Financial Status
            ['id' => 135, 'type' => 5, 'value_en' => 'Poor', 'value_bn' => 'দরিদ্র', 'default' => 1],
            ['id' => 136, 'type' => 5, 'value_en' => 'Refugee', 'value_bn' => 'উদ্বাস্তু', 'default' => 1],
            ['id' => 137, 'type' => 5, 'value_en' => 'Landless', 'value_bn' => 'ভূমিহীন', 'default' => 1],

            // Relations With Bef
            ['id' => 138, 'type' => 38, 'value_en' => 'Spouse', 'value_bn' => 'স্বামী/স্ত্রী', 'default' => 1],
            ['id' => 139, 'type' => 38, 'value_en' => 'Family member', 'value_bn' => 'পরিবারের সদস্য', 'default' => 1],
            ['id' => 140, 'type' => 38, 'value_en' => 'Close relative', 'value_bn' => 'নিকট আত্মীয়', 'default' => 1],
            ['id' => 141, 'type' => 38, 'value_en' => 'Parent', 'value_bn' => 'পিতা/মাতা', 'default' => 1],

            // Mobile Ownership
            ['id' => 142, 'type' => 37, 'value_en' => 'Myself', 'value_bn' => 'নিজ', 'default' => 1],
            ['id' => 143, 'type' => 37, 'value_en' => 'Family Member', 'value_bn' => 'পরিবারের সদস্য', 'default' => 1],
            ['id' => 144, 'type' => 37, 'value_en' => 'Close Relative', 'value_bn' => 'নিকট আত্মীয়', 'default' => 1],

             // injury type
            ['id' => 145, 'type' => 40, 'value_en' => 'Temporary', 'value_bn' => 'সাময়িক', 'default' => 1],
            ['id' => 146, 'type' => 40, 'value_en' => 'Medium term', 'value_bn' => 'মধ্যমেয়াদি', 'default' => 1],
            ['id' => 147, 'type' => 40, 'value_en' => 'Forever', 'value_bn' => 'আজীবন', 'default' => 1],

            // Mobile Ownership
            ['id' => 148, 'type' => 37, 'value_en' => 'Others', 'value_bn' => 'অন্যান্য', 'default' => 1],
        ];

        // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // DB::table('lookups')->truncate();
        // foreach ($lookups as $value) {
        //     $lookup = new Lookup;
        //     $lookup->id = $value['id'];
        //     $lookup->type = $value['type'];
        //     $lookup->value_en = $value['value_en'];
        //     $lookup->value_bn = $value['value_bn'];
        //     $lookup->keyword = $value['keyword'] ?? null;
        //     $lookup->default = $value['default'];
        //     $lookup->save();
        // }
        // DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
