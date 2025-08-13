<?php

namespace Database\Seeders;

use App\Http\Traits\PermissionTrait;
use DB;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    use PermissionTrait;


    private $guard = 'sanctum';
    /**
     * Run the database seeds.
     *
     * @return void
     */


    ///LATEST MENU ID = 160
    //  IF YOU ADD A NEW Permission the ID will start from one greater than the LAST MENU ID

    public function run()
    {
        $per = ['create', 'list', 'edit', 'delete'];
        $permissions = [

            /* -------------------------------------------------------------------------- */
            /*                            system configuration                            */
            /* -------------------------------------------------------------------------- */
            [
                'module_name' => $this->modulePermissionSystemConfiguration,
                'sub_module_name' => $this->subDemographicInformationManagement,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 1, "group_dn_bn"=>"বিভাগ", "group_dn_en" => "Division", "name" => "division-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/system-configuration/division/create", "parent_page" => 1],
                    ["id" => 2, "group_dn_bn"=>"বিভাগ", "group_dn_en" => "Division", "name" => "division-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/system-configuration/division", "parent_page" => 1],
                    ["id" => 3, "group_dn_bn"=>"বিভাগ", "group_dn_en" => "Division", "name" => "division-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/system-configuration/division/edit/:id", "parent_page" => 1],
                    ["id" => 4, "group_dn_bn"=>"বিভাগ", "group_dn_en" => "Division", "name" => "division-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/system-configuration/division", "parent_page" => 1],

                    ["id" => 5, "group_dn_bn"=>"জেলা", "group_dn_en" => "District", "name" => "district-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/system-configuration/district/create", "parent_page" => 1],
                    ["id" => 6, "group_dn_bn"=>"জেলা", "group_dn_en" => "District", "name" => "district-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/system-configuration/district", "parent_page" => 1],
                    ["id" => 7, "group_dn_bn"=>"জেলা", "group_dn_en" => "District", "name" => "district-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/system-configuration/district/edit/:id", "parent_page" => 1],
                    ["id" => 8, "group_dn_bn"=>"জেলা", "group_dn_en" => "District", "name" => "district-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/system-configuration/district", "parent_page" => 1],

                    ["id" => 9, "group_dn_bn"=>"উপজেলা/সিটি কর্পোরেশন/জেলা পৌরসভা", "group_dn_en" => "Upazila/ City Corporation/ District Pouroshava", "name" => "city-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/system-configuration/city/create", "parent_page" => 1],
                    ["id" => 10, "group_dn_bn"=>"উপজেলা/সিটি কর্পোরেশন/জেলা পৌরসভা", "group_dn_en" => "Upazila/ City Corporation/ District Pouroshava", "name" => "city-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/system-configuration/city", "parent_page" => 1],
                    ["id" => 11, "group_dn_bn"=>"উপজেলা/সিটি কর্পোরেশন/জেলা পৌরসভা", "group_dn_en" => "Upazila/ City Corporation/ District Pouroshava", "name" => "city-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/system-configuration/city/edit/:id", "parent_page" => 1],
                    ["id" => 12, "group_dn_bn"=>"উপজেলা/সিটি কর্পোরেশন/জেলা পৌরসভা", "group_dn_en" => "Upazila/ City Corporation/ District Pouroshava", "name" => "city-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/system-configuration/city", "parent_page" => 1],

                    ["id" => 13, "group_dn_bn"=>"থানা", "group_dn_en" => "Thana", "name" => "thana-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/system-configuration/thana/create", "parent_page" => 1],
                    ["id" => 14, "group_dn_bn"=>"থানা", "group_dn_en" => "Thana", "name" => "thana-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/system-configuration/thana", "parent_page" => 1],
                    ["id" => 15, "group_dn_bn"=>"থানা", "group_dn_en" => "Thana", "name" => "thana-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/system-configuration/thana/edit/:id", "parent_page" => 1],
                    ["id" => 16, "group_dn_bn"=>"থানা", "group_dn_en" => "Thana", "name" => "thana-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/system-configuration/thana", "parent_page" => 1],

                    ["id" => 17, "group_dn_bn"=>"ইউনিয়ন", "group_dn_en" => "Union", "name" => "union-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/system-configuration/union/create", "parent_page" => 1],
                    ["id" => 18, "group_dn_bn"=>"ইউনিয়ন", "group_dn_en" => "Union", "name" => "union-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/system-configuration/union", "parent_page" => 1],
                    ["id" => 19, "group_dn_bn"=>"ইউনিয়ন", "group_dn_en" => "Union", "name" => "union-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/system-configuration/union/edit/:id", "parent_page" => 1],
                    ["id" => 20, "group_dn_bn"=>"ইউনিয়ন", "group_dn_en" => "Union", "name" => "union-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/system-configuration/union", "parent_page" => 1],

                    ["id" => 21, "group_dn_bn"=>"ওয়ার্ড", "group_dn_en" => "Ward", "name" => "ward-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/system-configuration/ward/create", "parent_page" => 1],
                    ["id" => 22, "group_dn_bn"=>"ওয়ার্ড", "group_dn_en" => "Ward", "name" => "ward-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/system-configuration/ward", "parent_page" => 1],
                    ["id" => 23, "group_dn_bn"=>"ওয়ার্ড", "group_dn_en" => "Ward", "name" => "ward-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/system-configuration/ward/edit/:id", "parent_page" => 1],
                    ["id" => 24, "group_dn_bn"=>"ওয়ার্ড", "group_dn_en" => "Ward", "name" => "ward-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/system-configuration/ward", "parent_page" => 1],

                ]
            ],

            [
                'module_name' => $this->modulePermissionSystemConfiguration,
                'sub_module_name' => $this->systemDashboard,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 195, "group_dn_bn"=>"সিস্টেম কনফিগারেশন ড্যাশবোর্ড", "group_dn_en" => "System Configuration Dashboard", "name" => "systemConfigurationDashboard-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/system-configuration/dashboard", "parent_page" => 1],
                    ["id" => 198, "group_dn_bn"=>"সিস্টেম কনফিগারেশন ড্যাশবোর্ড", "group_dn_en" => "System Configuration Dashboard", "name" => "systemConfigurationDashboard-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/system-configuration/dashboard", "parent_page" => 1]
                ]
            ],

            [
                'module_name' => $this->modulePermissionSystemConfiguration,
                'sub_module_name' => $this->subBanks,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 325, "group_dn_bn"=>"ব্যাংক", "group_dn_en" => "Bank", "name" => "bank-information", "dn_bn" =>"ব্যাংক তথ্য", "dn_en" => "Bank Information", "page_url" => "/common/all-banks", "parent_page" => 1],
                ]
            ],

            [
                'module_name' => $this->modulePermissionSystemConfiguration,
                'sub_module_name' => $this->subMfs,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 341, "group_dn_bn"=>"এমএফএস", "group_dn_en" => "MFS", "name" => "mfs-information", "dn_bn" =>"এমএফএস তথ্য", "dn_en" => "MFS Information", "page_url" => "/common/all-mfs", "parent_page" => 1],
                ]
            ],

            [
                'module_name' => $this->modulePermissionSystemConfiguration,
                'sub_module_name' => $this->subBranch,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 326, "group_dn_bn"=>"ব্যাংক শাখা", "group_dn_en" => "Barnch Information", "name" => "branch-information", "dn_bn" =>"শাখা", "dn_en" => "Branch", "page_url" => "/common/all-branch", "parent_page" => 1],
                ]
            ],

            [
                'module_name' => $this->modulePermissionSystemConfiguration,
                'sub_module_name' => $this->subAllowanceProgramManagement,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 25, "group_dn_bn"=>"ভাতা কার্যক্রম", "group_dn_en" => "Allowance Program", "name" => "allowance-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/system-configuration/allowance-program/create", "parent_page" => 0],
                    ["id" => 26, "group_dn_bn"=>"ভাতা কার্যক্রম", "group_dn_en" => "Allowance Program", "name" => "allowance-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/system-configuration/allowance-program", "parent_page" => 0],
                    ["id" => 27, "group_dn_bn"=>"ভাতা কার্যক্রম", "group_dn_en" => "Allowance Program", "name" => "allowance-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/system-configuration/allowance-program/edit/:id", "parent_page" => 0],
                    ["id" => 28, "group_dn_bn"=>"ভাতা কার্যক্রম", "group_dn_en" => "Allowance Program", "name" => "allowance-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/system-configuration/allowance-program", "parent_page" => 0],

                    ["id" => 191, "group_dn_bn"=>"ভাতা কার্যক্রম ফিল্ড", "group_dn_en" => "Allowance Program Fields", "name" => "allowanceField-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/system-configuration/allowance-program-additional-field", "parent_page" => 1],
                    ["id" => 180, "group_dn_bn"=>"ভাতা কার্যক্রম ফিল্ড", "group_dn_en" => "Allowance Program Fields", "name" => "allowanceField-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/system-configuration/allowance-program-additional-field", "parent_page" => 1],
                    ["id" => 192, "group_dn_bn"=>"ভাতা কার্যক্রম ফিল্ড", "group_dn_en" => "Allowance Program Fields", "name" => "allowanceField-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/system-configuration/allowance-program-additional-field", "parent_page" => 1],
                    ["id" => 193, "group_dn_bn"=>"ভাতা কার্যক্রম ফিল্ড", "group_dn_en" => "Allowance Program Fields", "name" => "allowanceField-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/system-configuration/allowance-program-additional-field", "parent_page" => 1],


                ]
            ],
            [
                'module_name' => $this->modulePermissionSystemConfiguration,
                'sub_module_name' => $this->subOfficeInformationManagement,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 29, "group_dn_bn"=>"অফিস", "group_dn_en" => "Office", "name" => "office-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/system-configuration/office/create", "parent_page" => 0],
                    ["id" => 30, "group_dn_bn"=>"অফিস", "group_dn_en" => "Office", "name" => "office-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/system-configuration/office", "parent_page" => 0],
                    ["id" => 31, "group_dn_bn"=>"অফিস", "group_dn_en" => "Office", "name" => "office-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/system-configuration/office/edit/:id", "parent_page" => 0],
                    ["id" => 32, "group_dn_bn"=>"অফিস", "group_dn_en" => "Office", "name" => "office-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/system-configuration/office", "parent_page" => 0],

                    ["id" => 363, "group_dn_bn"=>"অফিস", "group_dn_en" => "Office", "name" => "office-report", "dn_bn" =>"অফিস রিপোর্ট", "dn_en" => "Office Report", "page_url" => "/system-configuration/office-report", "parent_page" => 0]
                ]
            ],
            [
                'module_name' => $this->modulePermissionSystemConfiguration,
                'sub_module_name' => $this->subFinancialInformationManagement,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 33, "group_dn_bn"=>"অর্থ বছর", "group_dn_en" => "Financeial year", "name" => "financial-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/system-configuration/financial/create", "parent_page" => 0],
                    ["id" => 34, "group_dn_bn"=>"অর্থ বছর", "group_dn_en" => "Financeial year", "name" => "financial-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/system-configuration/financial", "parent_page" => 0],
                    ["id" => 35, "group_dn_bn"=>"অর্থ বছর", "group_dn_en" => "Financeial year", "name" => "financial-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/system-configuration/financial/edit/:id", "parent_page" => 0],
                    ["id" => 36, "group_dn_bn"=>"অর্থ বছর", "group_dn_en" => "Financeial year", "name" => "financial-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/system-configuration/financial", "parent_page" => 0]
                ]
            ],
            [
                'module_name' => $this->modulePermissionSystemConfiguration,
                'sub_module_name' => $this->subUserManagement,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 37, "group_dn_bn"=>"ব্যবহারকারী", "group_dn_en" => "User", "name" => "user-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/system-configuration/users/create", "parent_page" => 1],
                    ["id" => 38, "group_dn_bn"=>"ব্যবহারকারী", "group_dn_en" => "User", "name" => "user-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/system-configuration/users", "parent_page" => 1],
                    ["id" => 39, "group_dn_bn"=>"ব্যবহারকারী", "group_dn_en" => "User", "name" => "user-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/system-configuration/users/edit/:id", "parent_page" => 1],
                    ["id" => 40, "group_dn_bn"=>"ব্যবহারকারী", "group_dn_en" => "User", "name" => "user-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/system-configuration/users", "parent_page" => 1]
                ]
            ],

            [
                'module_name' => $this->modulePermissionSystemConfiguration,
                'sub_module_name' => $this->subUserManagement,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 41, "group_dn_bn"=>"রোল", "group_dn_en" => "Role", "name" => "role-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/system-configuration/role/create", "parent_page" => 1],
                    ["id" => 42, "group_dn_bn"=>"রোল", "group_dn_en" => "Role", "name" => "role-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/system-configuration/role", "parent_page" => 1],
                    ["id" => 43, "group_dn_bn"=>"রোল", "group_dn_en" => "Role", "name" => "role-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/system-configuration/role/edit/:id", "parent_page" => 1],
                    ["id" => 44, "group_dn_bn"=>"রোল", "group_dn_en" => "Role", "name" => "role-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/system-configuration/role", "parent_page" => 1],


                ]
            ],

            [
                'module_name' => $this->modulePermissionSystemConfiguration,
                'sub_module_name' => $this->subUserManagement,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 45, "group_dn_bn"=>"রোল অনুমতি", "group_dn_en" => "Role Permission", "name" => "rolePermission-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/system-configuration/role-permission", "parent_page" => 1],
                    ["id" => 190, "group_dn_bn"=>"রোল অনুমতি", "group_dn_en" => "Role Permission", "name" => "rolePermission-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/system-configuration/role-permission", "parent_page" => 1],
                    ["id" => 177, "group_dn_bn"=>"রোল অনুমতি", "group_dn_en" => "Role Permission", "name" => "rolePermission-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/system-configuration/role-permission", "parent_page" => 1],
                ]
            ],

            [
                'module_name' => $this->modulePermissionBudgetManagement,
                'sub_module_name' => $this->budgetManagement,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 46, "group_dn_bn"=>"বাজেট", "group_dn_en" => "Budget", "name" => "budget-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/budget/create", "parent_page" => 1],
                    ["id" => 47, "group_dn_bn"=>"বাজেট", "group_dn_en" => "Budget", "name" => "budget-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/budget", "parent_page" => 1],
                    ["id" => 48, "group_dn_bn"=>"বাজেট", "group_dn_en" => "Budget", "name" => "budget-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/budget/edit/:id", "parent_page" => 1],
                    ["id" => 49, "group_dn_bn"=>"বাজেট", "group_dn_en" => "Budget", "name" => "budget-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/budget", "parent_page" => 1],

                    ["id" => 301, "group_dn_bn"=>"বাজেট ড্যাসবোর্ড", "group_dn_en" => "Budget  Dashboard", "name" => "budgetDashboard-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/budget/dashboard/create", "parent_page" => 1],
                    ["id" => 302, "group_dn_bn"=>"বাজেট ড্যাসবোর্ড", "group_dn_en" => "Budget  Dashboard", "name" => "budgetDashboard-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/budget/dashboard", "parent_page" => 1],
                    ["id" => 303, "group_dn_bn"=>"বাজেট ড্যাসবোর্ড", "group_dn_en" => "Budget  Dashboard", "name" => "budgetDashboard-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/budget/dashboard/edit/:id", "parent_page" => 1],
                    ["id" => 304, "group_dn_bn"=>"বাজেট ড্যাসবোর্ড", "group_dn_en" => "Budget  Dashboard", "name" => "budgetDashboard-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/budget/dashboard", "parent_page" => 1],
                ]
            ],
            [
                'module_name' => $this->modulePermissionAllotmentManagement,
                'sub_module_name' => $this->allotmentManagement,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 50, "group_dn_bn"=>"বরাদ্দ", "group_dn_en" => "Allotment", "name" => "allotment-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/allotment/create", "parent_page" => 0],
                    ["id" => 51, "group_dn_bn"=>"বরাদ্দ", "group_dn_en" => "Allotment", "name" => "allotment-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/allotment", "parent_page" => 0],
                    ["id" => 52, "group_dn_bn"=>"বরাদ্দ", "group_dn_en" => "Allotment", "name" => "allotment-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/allotment/edit/:id", "parent_page" => 0],
                    ["id" => 53, "group_dn_bn"=>"বরাদ্দ", "group_dn_en" => "Allotment", "name" => "allotment-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/allotment", "parent_page" => 0]
                ]
            ],
            [
                'module_name' => $this->modulePermissionSystemConfiguration,
                'sub_module_name' => $this->menuManagement,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 54, "group_dn_bn"=>"মেনু", "group_dn_en" => "Menu", "name" => "menu-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/system-configuration/menu/create", "parent_page" => 0],
                    ["id" => 55, "group_dn_bn"=>"মেনু", "group_dn_en" => "Menu", "name" => "menu-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/system-configuration/menu", "parent_page" => 0],
                    ["id" => 56, "group_dn_bn"=>"মেনু", "group_dn_en" => "Menu", "name" => "menu-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/system-configuration/menu/edit/:id", "parent_page" => 0],
                    ["id" => 57, "group_dn_bn"=>"মেনু", "group_dn_en" => "Menu", "name" => "menu-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/system-configuration/menu", "parent_page" => 0]
                ]
            ],
            [
                'module_name' => $this->modulePermissionSystemConfiguration,
                'sub_module_name' => $this->deviceRegistrationManagement,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 58, "group_dn_bn"=>"ডিভাইস নিবন্ধন", "group_dn_en" => "Device registration", "name" => "device-registration-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/system-configuration/device-registration/create", "parent_page" => 0],
                    ["id" => 59, "group_dn_bn"=>"ডিভাইস নিবন্ধন", "group_dn_en" => "Device registration", "name" => "device-registration-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/system-configuration/device-registration", "parent_page" => 0],
                    ["id" => 60, "group_dn_bn"=>"ডিভাইস নিবন্ধন", "group_dn_en" => "Device registration", "name" => "device-registration-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/system-configuration/device-registration/edit/:id", "parent_page" => 0],
                    ["id" => 61, "group_dn_bn"=>"ডিভাইস নিবন্ধন", "group_dn_en" => "Device registration", "name" => "device-registration-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/system-configuration/device-registration", "parent_page" => 0]
                ]
            ],

            /* -------------------------------------------------------------------------- */
            /*                            Application Selection                           */
            /* -------------------------------------------------------------------------- */
            [
                'module_name' => $this->modulePermissionApplicationSelection,
                'sub_module_name' => $this->subOnlineApplicationManagement,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 62, "group_dn_bn"=>"অনলাইন আবেদন", "group_dn_en" => "Online application", "name" => "application-entry-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/application-management/application", "parent_page" => 1],
                    ["id" => 63, "group_dn_bn"=>"অনলাইন আবেদন", "group_dn_en" => "Online application", "name" => "application-entry-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/application-management/application/edit/:id", "parent_page" => 1],
                    ["id" => 64, "group_dn_bn"=>"অনলাইন আবেদন", "group_dn_en" => "Online application", "name" => "application-entry-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/application-management/application", "parent_page" => 1],
                    ["id" => 357, "group_dn_bn"=>"অনলাইন আবেদন", "group_dn_en" => "Online application", "name" => "application-entry-rejected-view", "dn_bn" =>"প্রত্যাখ্যাত তালিকা", "dn_en" => "Rejected List", "page_url" => "/application-management/application?type=rejected", "parent_page" => 1],
                    ["id" => 358, "group_dn_bn"=>"অনলাইন আবেদন", "group_dn_en" => "Online application", "name" => "application-entry-deleted-view", "dn_bn" =>"অপসারিত তালিকা", "dn_en" => "Deleted List", "page_url" => "/application-management/application?type=deleted", "parent_page" => 1],

                    ["id" => 194, "group_dn_bn"=>"আবেদন ড্যাসবোর্ড", "group_dn_en" => "Application dashboard", "name" => "applicationDashboard-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/application-management/dashboard", "parent_page" => 1],
                    ["id" => 197, "group_dn_bn"=>"আবেদন ড্যাসবোর্ড", "group_dn_en" => "Application dashboard", "name" => "applicationDashboard-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/application-management/dashboard", "parent_page" => 1],
                    ["id" => 344, "group_dn_bn"=>"আবেদন", "group_dn_en" => "Application", "name" => "application-tracking", "dn_bn" =>"ট্র্যাকিং", "dn_en" => "Tracking", "page_url" => "/system-audit/application-tracking", "parent_page" => 1],
                    ["id" => 345, "group_dn_bn"=>"আবেদন", "group_dn_en" => "Application", "name" => "application-approve", "dn_bn" =>"অনুমোদন", "dn_en" => "Approve", "page_url" => "#", "parent_page" => 1],
                    ["id" => 346, "group_dn_bn"=>"আবেদন", "group_dn_en" => "Application", "name" => "application-forward", "dn_bn" =>"ফরওয়ার্ড", "dn_en" => "Forward", "page_url" => "#", "parent_page" => 1],
                    ["id" => 347, "group_dn_bn"=>"আবেদন", "group_dn_en" => "Application", "name" => "application-reject", "dn_bn" =>"বাতিল", "dn_en" => "Reject", "page_url" => "#", "parent_page" => 1],
                    ["id" => 348, "group_dn_bn"=>"আবেদন", "group_dn_en" => "Application", "name" => "application-waiting", "dn_bn" =>"অপেক্ষমান", "dn_en" => "Waiting", "page_url" => "#", "parent_page" => 1],
                    ["id" => 349, "group_dn_bn"=>"আবেদন", "group_dn_en" => "Application", "name" => "application-recommendation", "dn_bn" =>"সুপারিশ", "dn_en" => "Recommendation", "page_url" => "#", "parent_page" => 1],
                    ["id" => 356, "group_dn_bn"=>"আবেদন", "group_dn_en" => "Application", "name" => "application-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "#", "parent_page" => 1],

                ]
            ],
            [
                'module_name' => $this->modulePermissionApplicationSelection,
                'sub_module_name' => $this->subPovertyScoreManagement,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 81, "group_dn_bn"=>"পিএমটি কাট-অফ স্কোর", "group_dn_en" => "Proverty Cut-off score", "name" => "poverty-cut-off-score-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/application-management/poverty-cut-off-score/create", "parent_page" => 1],
                    ["id" => 82, "group_dn_bn"=>"পিএমটি কাট-অফ স্কোর", "group_dn_en" => "Proverty Cut-off score", "name" => "poverty-cut-off-score-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/application-management/poverty-cut-off-score", "parent_page" => 1],
                    ["id" => 83, "group_dn_bn"=>"পিএমটি কাট-অফ স্কোর", "group_dn_en" => "Proverty Cut-off score", "name" => "poverty-cut-off-score-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/application-management/poverty-cut-off-score/edit/:id", "parent_page" => 1],
                    ["id" => 84, "group_dn_bn"=>"পিএমটি কাট-অফ স্কোর", "group_dn_en" => "Proverty Cut-off score", "name" => "poverty-cut-off-score-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/application-management/poverty-cut-off-score", "parent_page" => 1],

                    ["id" => 85, "group_dn_bn"=>"পিএমটি কাট-অফ স্কোর - জেলা", "group_dn_en" => "Proverty Cut-off score - District", "name" => "district-fixed-effect-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/application-management/district-fixed-effect/create", "parent_page" => 1],
                    ["id" => 86, "group_dn_bn"=>"পিএমটি কাট-অফ স্কোর - জেলা", "group_dn_en" => "Proverty Cut-off score - District", "name" => "district-fixed-effect-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/application-management/district-fixed-effect", "parent_page" => 1],
                    ["id" => 87, "group_dn_bn"=>"পিএমটি কাট-অফ স্কোর - জেলা", "group_dn_en" => "Proverty Cut-off score - District", "name" => "district-fixed-effect-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/application-management/district-fixed-effect/edit/:id", "parent_page" => 1],
                    ["id" => 88, "group_dn_bn"=>"পিএমটি কাট-অফ স্কোর - জেলা", "group_dn_en" => "Proverty Cut-off score - District", "name" => "district-fixed-effect-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/application-management/district-fixed-effect", "parent_page" => 1],

                    ["id" => 89, "group_dn_bn"=>"ভ্যারিয়েবল", "group_dn_en" => "Variable", "name" => "variable-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/application-management/variable/create", "parent_page" => 1],
                    ["id" => 90, "group_dn_bn"=>"ভ্যারিয়েবল", "group_dn_en" => "Variable", "name" => "variable-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/application-management/variable", "parent_page" => 1],
                    ["id" => 91, "group_dn_bn"=>"ভ্যারিয়েবল", "group_dn_en" => "Variable", "name" => "variable-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/application-management/variable/edit/:id", "parent_page" => 1],
                    ["id" => 92, "group_dn_bn"=>"ভ্যারিয়েবল", "group_dn_en" => "Variable", "name" => "variable-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/application-management/variable", "parent_page" => 1],

                    ["id" => 93, "group_dn_bn"=>"সাব ভ্যারিয়েবল", "group_dn_en" => "Sub Variable", "name" => "sub-variable-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/application-management/sub-variable/create", "parent_page" => 1],
                    ["id" => 94, "group_dn_bn"=>"সাব ভ্যারিয়েবল", "group_dn_en" => "Sub Variable", "name" => "sub-variable-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/application-management/sub-variable", "parent_page" => 1],
                    ["id" => 95, "group_dn_bn"=>"সাব ভ্যারিয়েবল", "group_dn_en" => "Sub Variable", "name" => "sub-variable-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/application-management/sub-variable/edit/:id", "parent_page" => 1],
                    ["id" => 96, "group_dn_bn"=>"সাব ভ্যারিয়েবল", "group_dn_en" => "Sub Variable", "name" => "sub-variable-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/application-management/sub-variable", "parent_page" => 1]

                ]
            ],


            /* -------------------------------------------------------------------------- */
            /*                           Beneficiary Management                           */
            /* -------------------------------------------------------------------------- */
            [
                'module_name' => $this->modulePermissionBeneficiaryManagement,
                'sub_module_name' => $this->subBeneficiaryInformationManagement,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 97, "group_dn_bn"=>"উপকারভোগীর তথ্য", "group_dn_en" => "Beneficiary Information", "name" => "beneficiaryInfo-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/beneficiary-management/beneficiary-info/create", "parent_page" => 1],
                    ["id" => 98, "group_dn_bn"=>"উপকারভোগীর তথ্য", "group_dn_en" => "Beneficiary Information", "name" => "beneficiaryInfo-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/beneficiary-management/beneficiary-info", "parent_page" => 1],
                    ["id" => 99, "group_dn_bn"=>"উপকারভোগীর তথ্য", "group_dn_en" => "Beneficiary Information", "name" => "beneficiaryInfo-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/beneficiary-management/beneficiary-info/edit/:id", "parent_page" => 1],
                    ["id" => 100, "group_dn_bn"=>"উপকারভোগীর তথ্য", "group_dn_en" => "Beneficiary Information", "name" => "beneficiaryInfo-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/beneficiary-management/beneficiary-info-delete", "parent_page" => 1],
                    ["id" => 352, "group_dn_bn"=>"উপকারভোগীর তথ্য", "group_dn_en" => "Beneficiary Information", "name" => "beneficiaryInfo-inactive", "dn_bn" =>"নিষ্ক্রিয়", "dn_en" => "Inactive", "page_url" => "/beneficiary-management/beneficiary-info", "parent_page" => 1],
                    ["id" => 354, "group_dn_bn"=>"উপকারভোগীর তথ্য", "group_dn_en" => "Beneficiary Information", "name" => "beneficiaryInfo-accountChangeVerify", "dn_bn" =>"অ্যাকাউন্ট যাচাই", "dn_en" => "Account Change Verify", "page_url" => "/beneficiary-management/beneficiary-info", "parent_page" => 1],
                    ["id" => 355, "group_dn_bn"=>"উপকারভোগীর তথ্য", "group_dn_en" => "Beneficiary Information", "name" => "beneficiaryInfo-accountChangeApprove", "dn_bn" =>"অ্যাকাউন্ট  অনুমোদন", "dn_en" => "Account Change Approve", "page_url" => "/beneficiary-management/beneficiary-info", "parent_page" => 1],
                    ["id" => 360, "group_dn_bn"=>"উপকারভোগীর তথ্য", "group_dn_en" => "Beneficiaries ", "name" => "beneficiaries-with-duplicate-account", "dn_bn" =>"সদৃশ হিসেবধারী উপকারভোগী", "dn_en" => "Beneficiaries with Duplicate Account", "page_url" => "/beneficiary-management/beneficiaries-with-duplicate-account", "parent_page" => 1],
                    ["id" => 361, "group_dn_bn"=>"উপকারভোগীর তথ্য", "group_dn_en" => "Beneficiaries ", "name" => "beneficiaryAccountChangeList-view", "dn_bn" =>"উপকারভোগী এ্যাকাউন্ট পরিবর্তনের তালিকা", "dn_en" => "Beneficiary Account Change List", "page_url" => "/beneficiary-management/account-change-list", "parent_page" => 1],

                    ["id" => 200, "group_dn_bn"=>"উপকারভোগীর সক্রিয় তালিকা", "group_dn_en" => "Beneficiary Active List", "name" => "beneficiaryActiveList-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/beneficiary-management/beneficiary-info-active/create", "parent_page" => 1],
                    ["id" => 186, "group_dn_bn"=>"উপকারভোগীর সক্রিয় তালিকা", "group_dn_en" => "Beneficiary Active List", "name" => "beneficiaryActiveList-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/beneficiary-management/beneficiary-info-active", "parent_page" => 1],
                    ["id" => 201, "group_dn_bn"=>"উপকারভোগীর সক্রিয় তালিকা", "group_dn_en" => "Beneficiary Active List", "name" => "beneficiaryActiveList-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/beneficiary-management/beneficiary-info-active/edit/:id", "parent_page" => 1],
                    ["id" => 202, "group_dn_bn"=>"উপকারভোগীর সক্রিয় তালিকা", "group_dn_en" => "Beneficiary Active List", "name" => "beneficiaryActiveList-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/beneficiary-management/beneficiary-info-active/delete/:id", "parent_page" => 1],

                    ["id" => 203, "group_dn_bn"=>"উপকারভোগীর নিষ্ক্রিয় তালিকা", "group_dn_en" => "Beneficiary Inactive List", "name" => "beneficiaryInactiveList-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/beneficiary-management/beneficiary-info-inactive/create", "parent_page" => 1],
                    ["id" => 187, "group_dn_bn"=>"উপকারভোগীর নিষ্ক্রিয় তালিকা", "group_dn_en" => "Beneficiary Inactive List", "name" => "beneficiaryInactiveList-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/beneficiary-management/beneficiary-info-inactive", "parent_page" => 1],
                    ["id" => 204, "group_dn_bn"=>"উপকারভোগীর নিষ্ক্রিয় তালিকা", "group_dn_en" => "Beneficiary Inactive List", "name" => "beneficiaryInactiveList-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/beneficiary-management/beneficiary-info-inactive/edit/:id", "parent_page" => 1],
                    ["id" => 205, "group_dn_bn"=>"উপকারভোগীর নিষ্ক্রিয় তালিকা", "group_dn_en" => "Beneficiary Inactive List", "name" => "beneficiaryInactiveList-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/beneficiary-management/beneficiary-info-inactive/delete/:id", "parent_page" => 1],

                    ["id" => 206, "group_dn_bn"=>"উপকারভোগীর অপেক্ষমান তালিকা", "group_dn_en" => "Beneficiary Waiting List", "name" => "beneficiaryWaitingList-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/beneficiary-management/beneficiary-info-waiting/create", "parent_page" => 1],
                    ["id" => 188, "group_dn_bn"=>"উপকারভোগীর অপেক্ষমান তালিকা", "group_dn_en" => "Beneficiary Waiting List", "name" => "beneficiaryWaitingList-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/beneficiary-management/beneficiary-info-waiting", "parent_page" => 1],
                    ["id" => 207, "group_dn_bn"=>"উপকারভোগীর অপেক্ষমান তালিকা", "group_dn_en" => "Beneficiary Waiting List", "name" => "beneficiaryWaitingList-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/beneficiary-management/beneficiary-info-waiting/edit/:id", "parent_page" => 1],
                    ["id" => 208, "group_dn_bn"=>"উপকারভোগীর অপেক্ষমান তালিকা", "group_dn_en" => "Beneficiary Waiting List", "name" => "beneficiaryWaitingList-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/beneficiary-management/beneficiary-info-waiting/delete/:id", "parent_page" => 1],

                    ["id" => 209, "group_dn_bn"=>"উপকারভোগীর ডিলিট তালিকা", "group_dn_en" => "Beneficiary Delete List", "name" => "beneficiaryDeleteList-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/beneficiary-management/beneficiary-info-delete/create", "parent_page" => 1],
                    ["id" => 189, "group_dn_bn"=>"উপকারভোগীর ডিলিট তালিকা", "group_dn_en" => "Beneficiary Delete List", "name" => "beneficiaryDeleteList-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/beneficiary-management/beneficiary-info-delete", "parent_page" => 1],
                    ["id" => 210, "group_dn_bn"=>"উপকারভোগীর ডিলিট তালিকা", "group_dn_en" => "Beneficiary Delete List", "name" => "beneficiaryDeleteList-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/beneficiary-management/beneficiary-info-delete/edit/:id", "parent_page" => 1],
                    ["id" => 211, "group_dn_bn"=>"উপকারভোগীর ডিলিট তালিকা", "group_dn_en" => "Beneficiary Delete List", "name" => "beneficiaryDeleteList-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/beneficiary-management/beneficiary-info-delete/delete/:id", "parent_page" => 1],

                    ["id" => 212, "group_dn_bn"=>"উপকারভোগী ড্যাশবোর্ড", "group_dn_en" => "Beneficiary Dashboard", "name" => "beneficiaryDashboard-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/beneficiary-management/dashboard/create", "parent_page" => 1],
                    ["id" => 196, "group_dn_bn"=>"উপকারভোগী ড্যাশবোর্ড", "group_dn_en" => "Beneficiary Dashboard", "name" => "beneficiaryDashboard-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/beneficiary-management/dashboard", "parent_page" => 1],
                    ["id" => 213, "group_dn_bn"=>"উপকারভোগী ড্যাশবোর্ড", "group_dn_en" => "Beneficiary Dashboard", "name" => "beneficiaryDashboard-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/beneficiary-management/dashboard/edit/:id", "parent_page" => 1],
                    ["id" => 214, "group_dn_bn"=>"উপকারভোগী ড্যাশবোর্ড", "group_dn_en" => "Beneficiary Dashboard", "name" => "beneficiaryDashboard-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/beneficiary-management/dashboard/delete/:id", "parent_page" => 1],

                    ["id" => 109, "group_dn_bn"=>"উপকারভোগীর প্রতিস্থাপন", "group_dn_en" => "Beneficiary Replacement", "name" => "beneficiaryReplacement-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/beneficiary-management/beneficiary-replacement/create", "parent_page" => 1],
                    ["id" => 110, "group_dn_bn"=>"উপকারভোগীর প্রতিস্থাপন", "group_dn_en" => "Beneficiary Replacement", "name" => "beneficiaryReplacement-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/beneficiary-management/beneficiary-replacement-list", "parent_page" => 1],
                    ["id" => 111, "group_dn_bn"=>"উপকারভোগীর প্রতিস্থাপন", "group_dn_en" => "Beneficiary Replacement", "name" => "beneficiaryReplacement-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/beneficiary-management/beneficiary-replacement/edit/:id", "parent_page" => 1],
                    ["id" => 112, "group_dn_bn"=>"উপকারভোগীর প্রতিস্থাপন", "group_dn_en" => "Beneficiary Replacement", "name" => "beneficiaryReplacement-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/beneficiary-management/beneficiary-replacement/delete/:id", "parent_page" => 1],

                    ["id" => 113, "group_dn_bn"=>"উপকারভোগীর আইডি কার্ড", "group_dn_en" => "Beneficiary ID Card", "name" => "beneficiaryIdCard-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/beneficiary-management/beneficiary-card/create", "parent_page" => 1],
                    ["id" => 114, "group_dn_bn"=>"উপকারভোগীর আইডি কার্ড", "group_dn_en" => "Beneficiary ID Card", "name" => "beneficiaryIdCard-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/beneficiary-management/beneficiary-card", "parent_page" => 1],
                    ["id" => 115, "group_dn_bn"=>"উপকারভোগীর আইডি কার্ড", "group_dn_en" => "Beneficiary ID Card", "name" => "beneficiaryIdCard-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/beneficiary-management/beneficiary-card/edit/:id", "parent_page" => 1],
                    ["id" => 116, "group_dn_bn"=>"উপকারভোগীর আইডি কার্ড", "group_dn_en" => "Beneficiary ID Card", "name" => "beneficiaryIdCard-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/beneficiary-management/beneficiary-card/delete/:id", "parent_page" => 1],

                    ["id" => 117, "group_dn_bn"=>"উপকারভোগীর স্থানান্তর", "group_dn_en" => "Beneficiary Shifting", "name" => "beneficiaryShifting-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/beneficiary-management/beneficiary-shifting/create", "parent_page" => 1],
                    ["id" => 118, "group_dn_bn"=>"উপকারভোগীর স্থানান্তর", "group_dn_en" => "Beneficiary Shifting", "name" => "beneficiaryShifting-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/beneficiary-management/beneficiary-shifting", "parent_page" => 1],
                    ["id" => 119, "group_dn_bn"=>"উপকারভোগীর স্থানান্তর", "group_dn_en" => "Beneficiary Shifting", "name" => "beneficiaryShifting-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/beneficiary-management/beneficiary-shifting/edit/:id", "parent_page" => 1],
                    ["id" => 120, "group_dn_bn"=>"উপকারভোগীর স্থানান্তর", "group_dn_en" => "Beneficiary Shifting", "name" => "beneficiaryShifting-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/beneficiary-management/beneficiary-shifting/delete/:id", "parent_page" => 1],

                    ["id" => 353, "group_dn_bn"=>"উপকারভোগীর অবস্থান স্থানান্তর", "group_dn_en" => "Beneficiary Location Shifting", "name" => "beneficiaryLocationShifting-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/beneficiary-management/beneficiary-location-shifting", "parent_page" => 1],

                    ["id" => 182, "group_dn_bn"=>"উপকারভোগী প্রস্থান", "group_dn_en" => "Beneficiary Exit", "name" => "beneficiaryExit-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/beneficiary-management/beneficiary-exit/create", "parent_page" => 1],
                    ["id" => 183, "group_dn_bn"=>"উপকারভোগী প্রস্থান", "group_dn_en" => "Beneficiary Exit", "name" => "beneficiaryExit-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/beneficiary-management/beneficiary-exit", "parent_page" => 1],
                    ["id" => 184, "group_dn_bn"=>"উপকারভোগী প্রস্থান", "group_dn_en" => "Beneficiary Exit", "name" => "beneficiaryExit-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/beneficiary-management/beneficiary-exit/edit/:id", "parent_page" => 1],
                    ["id" => 185, "group_dn_bn"=>"উপকারভোগী প্রস্থান", "group_dn_en" => "Beneficiary Exit", "name" => "beneficiaryExit-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/beneficiary-management/beneficiary-exit/delete/:id", "parent_page" => 1],
                    ["id" => 351, "group_dn_bn"=>"উপকারভোগী প্রস্থান", "group_dn_en" => "Beneficiary Exit", "name" => "beneficiaryExit-rollback", "dn_bn" =>"রোলব্যাক", "dn_en" => "Rollback", "page_url" => "/beneficiary-management/beneficiary-exit/delete/:id", "parent_page" => 1],

                    ["id" => 101, "group_dn_bn"=>"কমিটি", "group_dn_en" => "Committee", "name" => "committee-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/beneficiary-management/committee/create", "parent_page" => 1],
                    ["id" => 102, "group_dn_bn"=>"কমিটি", "group_dn_en" => "Committee", "name" => "committee-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/beneficiary-management/committee", "parent_page" => 1],
                    ["id" => 103, "group_dn_bn"=>"কমিটি", "group_dn_en" => "Committee", "name" => "committee-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/beneficiary-management/committee/edit/:id", "parent_page" => 1],
                    ["id" => 104, "group_dn_bn"=>"কমিটি", "group_dn_en" => "Committee", "name" => "committee-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/beneficiary-management/committee", "parent_page" => 1]
                ]
            ],
            [
                'module_name' => $this->modulePermissionBeneficiaryManagement,
                'sub_module_name' => $this->subAllocationInformation,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 105, "group_dn_bn"=>"কমিটি অনুমতি", "group_dn_en" => "Committee Permission", "name" => "allocation-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/beneficiary-management/allocation/create", "parent_page" => 1],
                    ["id" => 106, "group_dn_bn"=>"কমিটি অনুমতি", "group_dn_en" => "Committee Permission", "name" => "allocation-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/beneficiary-management/allocation", "parent_page" => 1],
                    ["id" => 107, "group_dn_bn"=>"কমিটি অনুমতি", "group_dn_en" => "Committee Permission", "name" => "allocation-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/beneficiary-management/allocation/edit/:id", "parent_page" => 1],
                    ["id" => 108, "group_dn_bn"=>"কমিটি অনুমতি", "group_dn_en" => "Committee Permission", "name" => "allocation-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/beneficiary-management/allocation", "parent_page" => 1]
                ]
            ],


            /* -------------------------------------------------------------------------- */
            /*                             Payroll Management                             */
            /* -------------------------------------------------------------------------- */
            [
                'module_name' => $this->modulePermissionPayrollManagement,
                'sub_module_name' => $this->subPaymentProcessorInformation,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 121, "group_dn_bn"=>"পেমেন্ট প্রসেসর (এলাকাভিত্তিক)", "group_dn_en" => "Payment Processor (Area Wise)", "name" => "payment-processor-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/payroll-management/payment-processor", "parent_page" => 1],
                    ["id" => 122, "group_dn_bn"=>"পেমেন্ট প্রসেসর (এলাকাভিত্তিক)", "group_dn_en" => "Payment Processor (Area Wise)", "name" => "payment-processor-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/payroll-management/payment-processor", "parent_page" => 1],
                    ["id" => 123, "group_dn_bn"=>"পেমেন্ট প্রসেসর (এলাকাভিত্তিক)", "group_dn_en" => "Payment Processor (Area Wise)", "name" => "payment-processor-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/payroll-management/payment-processor", "parent_page" => 1],
                    ["id" => 124, "group_dn_bn"=>"পেমেন্ট প্রসেসর (এলাকাভিত্তিক)", "group_dn_en" => "Payment Processor (Area Wise)", "name" => "payment-processor-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/payroll-management/payment-processor", "parent_page" => 1]
                ]
            ],
            [
                'module_name' => $this->modulePermissionPayrollManagement,
                'sub_module_name' => $this->subPayrollCreate,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 298, "group_dn_bn"=>"পেরোল ক্রিয়েট", "group_dn_en" => "Payroll Create", "name" => "payroll-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/payroll-management/payroll-create", "parent_page" => 1],
                    ["id" => 291, "group_dn_bn"=>"পেরোল ক্রিয়েট", "group_dn_en" => "Payroll Create", "name" => "payroll-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/payroll-management/payroll/view/:id", "parent_page" => 1],
                    ["id" => 292, "group_dn_bn"=>"পেরোল ক্রিয়েট", "group_dn_en" => "Payroll Create", "name" => "payroll-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/payroll-management/payroll/edit/:id", "parent_page" => 1],
                    ["id" => 293, "group_dn_bn"=>"পেরোল ক্রিয়েট", "group_dn_en" => "Payroll Create", "name" => "payroll-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/payroll-management/payroll/delete/:id", "parent_page" => 1],


                ]
            ],
            [
                'module_name' => $this->modulePermissionPayrollManagement,
                'sub_module_name' => $this->subPayrollList,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 294, "group_dn_bn"=>"পেরোল তালিকা", "group_dn_en" => "Payroll List", "name" => "payroll-approval-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/payroll-management/payroll-approval/create", "parent_page" => 1],
                    ["id" => 295, "group_dn_bn"=>"পেরোল তালিকা", "group_dn_en" => "Payroll List", "name" => "payroll-approval-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/payroll-management/payroll-approval", "parent_page" => 1],
                    ["id" => 296, "group_dn_bn"=>"পেরোল তালিকা", "group_dn_en" => "Payroll List", "name" => "payroll-approval-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/payroll-management/payroll-approval/edit/:id", "parent_page" => 1],
                    ["id" => 297, "group_dn_bn"=>"পেরোল তালিকা", "group_dn_en" => "Payroll List", "name" => "payroll-approval-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/payroll-management/payroll-approval/delete/:id", "parent_page" => 1],

                    ["id" => 330, "group_dn_bn"=>"পেরোল তালিকা", "group_dn_en" => "Payroll List", "name" => "payroll-button-approve", "dn_bn" =>"অনুমোদন", "dn_en" => "Approve", "page_url" => "/payroll-management/payroll-approval", "parent_page" => 1],
                    ["id" => 331, "group_dn_bn"=>"পেরোল তালিকা", "group_dn_en" => "Payroll List", "name" => "payroll-button-reject", "dn_bn" =>"বাতিল", "dn_en" => "Regect", "page_url" => "/payroll-management/payroll-approval", "parent_page" => 1],
                    ["id" => 332, "group_dn_bn"=>"পেরোল তালিকা", "group_dn_en" => "Payroll List", "name" => "payroll-button-verify", "dn_bn" =>"যাচাই", "dn_en" => "Verify", "page_url" => "/payroll-management/payroll-approval", "parent_page" => 1],
                    ["id" => 333, "group_dn_bn"=>"পেরোল তালিকা", "group_dn_en" => "Payroll List", "name" => "payroll-button-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/payroll-management/payroll-approval", "parent_page" => 1],
                ]
            ],
            // Payroll Reconciliation Data Pull
            [
                'module_name' => $this->modulePermissionPayrollManagement,
                'sub_module_name' => $this->subPayrollReconciliationDataPull,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 327, "group_dn_bn"=>"পেরোল রিকন্সিলিয়েশন ডেটা পুল", "group_dn_en" => "Payroll Reconciliation Data Pull", "name" => "payroll-reconciliation-data-pull-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/payroll-management/payroll-reconciliation-data-pull/create", "parent_page" => 1],
                    ["id" => 328, "group_dn_bn"=>"পেরোল রিকন্সিলিয়েশন ডেটা পুল", "group_dn_en" => "Payroll Reconciliation Data Pull", "name" => "payroll-reconciliation-data-pull-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/payroll-management/payroll-reconciliation-data-pull", "parent_page" => 1],
                    ["id" => 334, "group_dn_bn"=>"পেরোল রিকন্সিলিয়েশন ডেটা পুল", "group_dn_en" => "Payroll Reconciliation Data Pull", "name" => "payroll-reconciliation-data-pull", "dn_bn" =>"ডেটা পুল", "dn_en" => "Data Pull", "page_url" => "/payroll-management/payroll-reconciliation-data-pull", "parent_page" => 1],
                ]
            ],

            /* -------------------------------------------------------------------------- */
            /*                            Emergency Payment Management                    */
            /* -------------------------------------------------------------------------- */
            // Emergency Payroll Reconciliation Data Pull
            [
                'module_name' => $this->modulePermissionPayrollManagement,
                'sub_module_name' => $this->subEmergencyPayrollReconciliationDataPull,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 335, "group_dn_bn"=>"জরুরী পেরোল রিকন্সিলিয়েশন ডেটা পুল", "group_dn_en" => "Emergency Payroll Reconciliation Data Pull", "name" => "emergency-payroll-reconciliation-data-pull-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/emergency-payment/payroll-reconciliation-data-pull/create", "parent_page" => 1],
                    ["id" => 336, "group_dn_bn"=>"জরুরী পেরোল রিকন্সিলিয়েশন ডেটা পুল", "group_dn_en" => "Emergency Payroll Reconciliation Data Pull", "name" => "emergency-payroll-reconciliation-data-pull-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/emergency-payment/payroll-reconciliation-data-pull", "parent_page" => 1],
                    ["id" => 337, "group_dn_bn"=>"জরুরী পেরোল রিকন্সিলিয়েশন ডেটা পুল", "group_dn_en" => "Emergency Payroll Reconciliation Data Pull", "name" => "emergency-payroll-reconciliation-data-pull", "dn_bn" =>"ডেটা পুল", "dn_en" => "Data Pull", "page_url" => "/emergency-payment/payroll-reconciliation-data-pull", "parent_page" => 1],
                ]
            ],
            //  Emergency Allotment
            [
                'module_name' => $this->modulePermissionEmergencyPayment,
                'sub_module_name' => $this->subEmergencyAllotment,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 133, "group_dn_bn"=>"জরুরী বরাদ্দ", "group_dn_en" => "Emergency Allotment", "name" => "emergency-allotment-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/emergency-payment/emergency-allotment/create", "parent_page" => 1],
                    ["id" => 134, "group_dn_bn"=>"জরুরী বরাদ্দ", "group_dn_en" => "Emergency Allotment", "name" => "emergency-allotment-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/emergency-payment/emergency-allotment", "parent_page" => 1],
                    ["id" => 135, "group_dn_bn"=>"জরুরী বরাদ্দ", "group_dn_en" => "Emergency Allotment", "name" => "emergency-allotment-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/emergency-payment/emergency-allotment/edit/:id", "parent_page" => 1],
                    ["id" => 136, "group_dn_bn"=>"জরুরী বরাদ্দ", "group_dn_en" => "Emergency Allotment", "name" => "emergency-allotment-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/emergency-payment/emergency-allotment/delete/:id", "parent_page" => 1],

                ]
            ],
            //  Emergency Beneficiary
            [
                'module_name' => $this->modulePermissionEmergencyPayment,
                'sub_module_name' => $this->subEmergencyBeneficiary,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 137, "group_dn_bn"=>"জরুরী উপকারভোগী", "group_dn_en" => "Emergency Beneficiary", "name" => "emergency-beneficiary-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/emergency-payment/emergency-beneficiary/create", "parent_page" => 1],
                    ["id" => 138, "group_dn_bn"=>"জরুরী উপকারভোগী", "group_dn_en" => "Emergency Beneficiary", "name" => "emergency-beneficiary-list", "dn_bn" =>"তালিকা", "dn_en" => "List", "page_url" => "/emergency-payment/emergency-beneficiary", "parent_page" => 1],
                    ["id" => 329, "group_dn_bn"=>"জরুরী উপকারভোগী", "group_dn_en" => "Emergency Beneficiary", "name" => "emergency-beneficiary-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/emergency-payment/emergency-beneficiary/detail/:id", "parent_page" => 1],
                    ["id" => 139, "group_dn_bn"=>"জরুরী উপকারভোগী", "group_dn_en" => "Emergency Beneficiary", "name" => "emergency-beneficiary-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/emergency-payment/emergency-beneficiary/edit/:id", "parent_page" => 1],
                    ["id" => 140, "group_dn_bn"=>"জরুরী উপকারভোগী", "group_dn_en" => "Emergency Beneficiary", "name" => "emergency-beneficiary-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/emergency-payment/emergency-beneficiary", "parent_page" => 1],

                ]
            ],

            //  Emergency Payment cycle
            [
                'module_name' => $this->modulePermissionEmergencyPayment,
                'sub_module_name' => $this->subEmergencyPaymentCycle,
                'guard_name' => $this->guard,
                'permissions' => [

                    ["id" => 286, "group_dn_bn"=>"জরুরী পেমেন্ট সাইকেল", "group_dn_en" => "Emergency Payment Cycle", "name" => "emergency-payment-cycle-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/emergency-payment/payment-cycle", "parent_page" => 1],
                    ["id" => 287, "group_dn_bn"=>"জরুরী পেমেন্ট সাইকেল", "group_dn_en" => "Emergency Payment Cycle", "name" => "emergency-payment-cycle-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/emergency-payment/cycle/view/:id", "parent_page" => 1],
                    ["id" => 299, "group_dn_bn"=>"জরুরী পেমেন্ট সাইকেল", "group_dn_en" => "Emergency Payment Cycle", "name" => "emergency-payment-cycle-reject", "dn_bn" =>"বাতিল", "dn_en" => "Reject", "page_url" => "/emergency-payment/cycle/reject/:id", "parent_page" => 1],
                    ["id" => 300, "group_dn_bn"=>"জরুরী পেমেন্ট সাইকেল", "group_dn_en" => "Emergency Payment Cycle", "name" => "emergency-payment-cycle-send", "dn_bn" =>"প্রেরণ", "dn_en" => "Send", "page_url" => "/emergency-payment/payment-cycle", "parent_page" => 1],

                ]
            ],
            // Emergency Payment reconciliation cycle
            [
                'module_name' => $this->modulePermissionEmergencyPayment,
                'sub_module_name' => $this->subEmergencyPaymentReconciliation,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 288, "group_dn_bn"=>"জরুরী পেরোল রিকন্সিলিয়েশন সমন্বয়", "group_dn_en" => "Emergency Payroll Reconciliation (Coordination)", "name" => "emergency-payment-reconciliation-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/emergency-payment/payroll-reconciliation", "parent_page" => 1],
                    ["id" => 305, "group_dn_bn"=>"জরুরী পেরোল রিকন্সিলিয়েশন সমন্বয়", "group_dn_en" => "Emergency Payment Reconciliation", "name" => "emergency-reconciliation-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/emergency-payment/reconciliation/edit/:id", "parent_page" => 1],
                    ["id" => 306, "group_dn_bn"=>"জরুরী পেরোল রিকন্সিলিয়েশন সমন্বয়", "group_dn_en" => "Emergency Payroll Reconciliation (Coordination)", "name" => "emergency-payment-reconciliation-reject", "dn_bn" =>"বাতিল", "dn_en" => "Reject", "page_url" => "/emergency-payment/payroll-reconciliation", "parent_page" => 1],
                    ["id" => 307, "group_dn_bn"=>"জরুরী পেরোল রিকন্সিলিয়েশন সমন্বয়", "group_dn_en" => "Emergency Payroll Reconciliation (Coordination)", "name" => "emergency-payment-reconciliation-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/emergency-payment/payroll-reconciliation", "parent_page" => 1],
                    ["id" => 308, "group_dn_bn"=>"জরুরী পেরোল রিকন্সিলিয়েশন সমন্বয়", "group_dn_en" => "Emergency Payroll Reconciliation (Coordination)", "name" => "emergency-payment-reconciliation-update", "dn_bn" =>"আপডেট", "dn_en" => "Update", "page_url" => "/emergency-payment/payroll-reconciliation", "parent_page" => 1],
                ]
            ],
            //  Manage Emergency Beneficiary
            [
                'module_name' => $this->modulePermissionEmergencyPayment,
                'sub_module_name' => $this->subManageEmergencyBeneficiary,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 141, "group_dn_bn"=>"জরুরী উপকারভোগী ব্যবস্থাপনা", "group_dn_en" => "Manage Emergency Beneficiary", "name" => "manage-emergency-beneficiary-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/emergency-payment/manage-emergency-beneficiary/create", "parent_page" => 1],
                    ["id" => 142, "group_dn_bn"=>"জরুরী উপকারভোগী ব্যবস্থাপনা", "group_dn_en" => "Manage Emergency Beneficiary", "name" => "manage-emergency-beneficiary-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/emergency-payment/manage-emergency-beneficiary", "parent_page" => 1],
                    ["id" => 143, "group_dn_bn"=>"জরুরী উপকারভোগী ব্যবস্থাপনা", "group_dn_en" => "Manage Emergency Beneficiary", "name" => "manage-emergency-beneficiary-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/emergency-payment/manage-emergency-beneficiary/edit/:id", "parent_page" => 1],
                    ["id" => 144, "group_dn_bn"=>"জরুরী উপকারভোগী ব্যবস্থাপনা", "group_dn_en" => "Manage Emergency Beneficiary", "name" => "manage-emergency-beneficiary-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/emergency-payment/manage-emergency-beneficiary", "parent_page" => 1],

                ]
            ],
            //  Payroll Create
            [
                'module_name' => $this->modulePermissionEmergencyPayment,
                'sub_module_name' => $this->subEmergencyPayorll,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 280, "group_dn_bn"=>"জরুরী পেরোল", "group_dn_en" => "Emergency Payroll", "name" => "emergency-payroll-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/emergency-payment/emergency-payroll/create", "parent_page" => 1],
                    ["id" => 281, "group_dn_bn"=>"জরুরী পেরোল", "group_dn_en" => "Emergency Payroll", "name" => "emergency-payroll-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/emergency-payment/emergency-payroll", "parent_page" => 1],
                    ["id" => 282, "group_dn_bn"=>"জরুরী পেরোল", "group_dn_en" => "Emergency Payroll", "name" => "emergency-payroll-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/emergency-payment/emergency-payroll/edit/:id", "parent_page" => 1],
                    ["id" => 283, "group_dn_bn"=>"জরুরী পেরোল", "group_dn_en" => "Emergency Payroll", "name" => "emergency-payroll-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/emergency-payment/emergency-payroll", "parent_page" => 1],
                    ["id" => 284, "group_dn_bn"=>"জরুরী পেরোল", "group_dn_en" => "Emergency Payroll", "name" => "emergency-payroll-approval", "dn_bn" =>"অনুমোদন", "dn_en" => "Approval", "page_url" => "/emergency-payment/emergency-payroll/approval", "parent_page" => 1],

                ]
            ],
            // Emergency Payroll Reconciliation Data Pull
            [
                'module_name' => $this->modulePermissionEmergencyPayment,
                'sub_module_name' => $this->subEmergencyPayorllReconciliationDataPull,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 311, "group_dn_bn"=>"জরুরী পেরোল রিকন্সিলিয়েশন ডেটা পুল", "group_dn_en" => "Emergency Payroll Reconciliation (Data Pull)", "name" => "emergency-payroll-reconciliation-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/emergency-payment/emergency-payroll-reconciliation/create", "parent_page" => 1],
                    ["id" => 312, "group_dn_bn"=>"জরুরী পেরোল রিকন্সিলিয়েশন ডেটা পুল", "group_dn_en" => "Emergency Payroll Reconciliation (Data Pull)", "name" => "emergency-payroll-reconciliation-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/emergency-payment/emergency-payroll-reconciliation", "parent_page" => 1],
                    ["id" => 313, "group_dn_bn"=>"জরুরী পেরোল রিকন্সিলিয়েশন ডেটা পুল", "group_dn_en" => "Emergency Payroll Reconciliation (Data Pull)", "name" => "emergency-payroll-reconciliation-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/emergency-payment/emergency-payroll-reconciliation/edit/:id", "parent_page" => 1],
                    ["id" => 314, "group_dn_bn"=>"জরুরী পেরোল রিকন্সিলিয়েশন ডেটা পুল", "group_dn_en" => "Emergency Payroll Reconciliation (Data Pull)", "name" => "emergency-payroll-reconciliation-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/emergency-payment/emergency-payroll-reconciliation", "parent_page" => 1],
                ]
            ],

            /* -------------------------------------------------------------------------- */
            /*                            Grievance Management                            */
            /* -------------------------------------------------------------------------- */

            [
                'module_name' => $this->modulePermissionGrievanceManagement,
                'sub_module_name' => $this->subGrievanceSetting,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 145, "group_dn_bn"=>"অভিযোগ সেটিংস", "group_dn_en" => "Grievance Settings", "name" => "grievanceSetting-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/grievance-management/settings", "parent_page" => 1],
                    ["id" => 146, "group_dn_bn"=>"অভিযোগ সেটিংস", "group_dn_en" => "Grievance Settings", "name" => "grievanceSetting-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/grievance-management/grievance-setting", "parent_page" => 1],
                    ["id" => 147, "group_dn_bn"=>"অভিযোগ সেটিংস", "group_dn_en" => "Grievance Settings", "name" => "grievanceSetting-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/grievance-management/grievance-setting/edit/:id", "parent_page" => 1],
                    ["id" => 148, "group_dn_bn"=>"অভিযোগ সেটিংস", "group_dn_en" => "Grievance Settings", "name" => "grievanceSetting-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/grievance-management/grievance-setting", "parent_page" => 1]
                ]
            ],
            [
                'module_name' => $this->modulePermissionGrievanceManagement,
                'sub_module_name' => $this->subGrievanceList,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 149, "group_dn_bn"=>"অভিযোগ তালিকা", "group_dn_en" => "Grievance List", "name" => "grievanceList-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/grievance-management/grievance-list", "parent_page" => 1],
                    ["id" => 150, "group_dn_bn"=>"অভিযোগ তালিকা", "group_dn_en" => "Grievance List", "name" => "grievanceList-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/grievance-management/grievance-list", "parent_page" => 1],
                    ["id" => 151, "group_dn_bn"=>"অভিযোগ তালিকা", "group_dn_en" => "Grievance List", "name" => "grievanceList-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/grievance-management/list/edit/:id", "parent_page" => 1],
                    ["id" => 152, "group_dn_bn"=>"অভিযোগ তালিকা", "group_dn_en" => "Grievance List", "name" => "grievanceList-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/grievance-management/list", "parent_page" => 1]
                ]
            ],
            [
                'module_name' => $this->modulePermissionGrievanceManagement,
                'sub_module_name' => $this->subGrievanceDashboard,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 215, "group_dn_bn"=>"অভিযোগ ব্যবস্থাপনা ড্যাশবোর্ড", "group_dn_en" => "Grievance Management Dashboard", "name" => "grievanceDashboard-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/grievance/dashboard", "parent_page" => 1],
                ]
            ],
            [
                'module_name' => $this->modulePermissionGrievanceManagement,
                'sub_module_name' => $this->subGrievanceType,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 217, "group_dn_bn"=>"অভিযোগের ধরণ", "group_dn_en" => "Grievance Type", "name" => "grievanceType-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/grievance-management/type", "parent_page" => 1],
                    ["id" => 218, "group_dn_bn"=>"অভিযোগের ধরণ", "group_dn_en" => "Grievance Type", "name" => "grievanceType-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/grievance-management/type-view", "parent_page" => 1],
                    ["id" => 219, "group_dn_bn"=>"অভিযোগের ধরণ", "group_dn_en" => "Grievance Type", "name" => "grievanceType-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/grievance-management/type/edit/:id", "parent_page" => 1],
                    ["id" => 220, "group_dn_bn"=>"অভিযোগের ধরণ", "group_dn_en" => "Grievance Type", "name" => "grievanceType-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/grievance-management/type-delete", "parent_page" => 1]
                ]
            ],
            [
                'module_name' => $this->modulePermissionGrievanceManagement,
                'sub_module_name' => $this->subGrievanceSubject,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 221, "group_dn_bn"=>"অভিযোগের বিষয়", "group_dn_en" => "Grievance Subject", "name" => "grievanceSubject-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/grievance-management/subject", "parent_page" => 1],
                    ["id" => 222, "group_dn_bn"=>"অভিযোগের বিষয়", "group_dn_en" => "Grievance Subject", "name" => "grievanceSubject-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/grievance-management/subject-view", "parent_page" => 1],
                    ["id" => 223, "group_dn_bn"=>"অভিযোগের বিষয়", "group_dn_en" => "Grievance Subject", "name" => "grievanceSubject-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/grievance-management/subject/edit/:id", "parent_page" => 1],
                    ["id" => 224, "group_dn_bn"=>"অভিযোগের বিষয়", "group_dn_en" => "Grievance Subject", "name" => "grievanceSubject-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/grievance-management/subject-delete", "parent_page" => 1]
                ]
            ],


            /* -------------------------------------------------------------------------- */
            /*                              Reporting System                              */
            /* -------------------------------------------------------------------------- */
            [
                'module_name' => $this->modulePermissionReportingSystem,
                'sub_module_name' => $this->modulePermissionReportingSystem,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 153, "group_dn_bn"=>"রিপোর্টিং তালিকা", "group_dn_en" => "Reporting List", "name" => "reporting-list-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/reporting-management/reporting-list/create", "parent_page" => 1],
                    ["id" => 154, "group_dn_bn"=>"রিপোর্টিং তালিকা", "group_dn_en" => "Reporting List", "name" => "reporting-list-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/reporting-management/reporting-list", "parent_page" => 1],
                    ["id" => 155, "group_dn_bn"=>"রিপোর্টিং তালিকা", "group_dn_en" => "Reporting List", "name" => "reporting-list-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/reporting-management/reporting-list/edit/:id", "parent_page" => 1],
                    ["id" => 156, "group_dn_bn"=>"রিপোর্টিং তালিকা", "group_dn_en" => "Reporting List", "name" => "reporting-list-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/reporting-management/reporting-list/delete", "parent_page" => 1],

                    ["id" => 157, "group_dn_bn"=>"সার্ভে রিপোর্টিং", "group_dn_en" => "Survey Reporting", "name" => "reporting-survey-create", "dn_bn" =>"সার্ভে ক্রিয়েট", "dn_en" => "Survey Create", "page_url" => "/reporting-management/reporting-survey", "parent_page" => 1],
                    ["id" => 158, "group_dn_bn"=>"সার্ভে রিপোর্টিং", "group_dn_en" => "Survey Reporting", "name" => "reporting-survey-view", "dn_bn" =>"সার্ভে ভিউ", "dn_en" => "Survey View", "page_url" => "/reporting-management/reporting-survey", "parent_page" => 1],
                    ["id" => 159, "group_dn_bn"=>"সার্ভে রিপোর্টিং", "group_dn_en" => "Survey Reporting", "name" => "reporting-survey-edit", "dn_bn" =>"সার্ভে এডিট", "dn_en" => "Survey Edit", "page_url" => "/reporting-management/reporting-survey/edit/:id", "parent_page" => 1],
                    ["id" => 160, "group_dn_bn"=>"সার্ভে রিপোর্টিং", "group_dn_en" => "Survey Reporting", "name" => "reporting-survey-delete", "dn_bn" =>"সার্ভে ডিলিট", "dn_en" => "Survey Delete", "page_url" => "/reporting-management/reporting-survey/delete", "parent_page" => 1],

                    ["id" => 161, "group_dn_bn"=>"রিপোর্টিং রিপোর্ট", "group_dn_en" => "Reporting Report", "name" => "reporting-report-create", "dn_bn" =>"রিপোর্টিং ক্রিয়েট", "dn_en" => "Reporting Create", "page_url" => "/reporting-management/reporting-report", "parent_page" => 1],
                    ["id" => 162, "group_dn_bn"=>"রিপোর্টিং রিপোর্ট", "group_dn_en" => "Reporting Report", "name" => "reporting-report-view", "dn_bn" =>"রিপোর্টিং ভিউ", "dn_en" => "Reporting View", "page_url" => "/reporting-management/reporting-report", "parent_page" => 1],
                    ["id" => 163, "group_dn_bn"=>"রিপোর্টিং রিপোর্ট", "group_dn_en" => "Reporting Report", "name" => "reporting-report-edit", "dn_bn" =>"রিপোর্টিং এডিট", "dn_en" => "Reporting Edit", "page_url" => "/reporting-management/reporting-report/edit/:id", "parent_page" => 1],
                    ["id" => 164, "group_dn_bn"=>"রিপোর্টিং রিপোর্ট", "group_dn_en" => "Reporting Report", "name" => "reporting-report-delete", "dn_bn" =>"রিপোর্টিং ডিলিট", "dn_en" => "Reporting Delete", "page_url" => "/reporting-management/reporting-report/delete", "parent_page" => 1],

                    ["id" => 165, "group_dn_bn"=>"পাওয়ার বিআই রিপোর্ট ", "group_dn_en" => "Power BI Report", "name" => "bireport-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/reports/bi-report", "parent_page" => 1],
                    ["id" => 166, "group_dn_bn"=>"পাওয়ার বিআই রিপোর্ট ", "group_dn_en" => "Power BI Report", "name" => "bireport-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/reports/bi-report/:id", "parent_page" => 1],
                    ["id" => 167, "group_dn_bn"=>"পাওয়ার বিআই রিপোর্ট ", "group_dn_en" => "Power BI Report", "name" => "bireport-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/reports/bi-report/:id", "parent_page" => 1],
                    ["id" => 168, "group_dn_bn"=>"পাওয়ার বিআই রিপোর্ট ", "group_dn_en" => "Power BI Report", "name" => "bireport-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/reports/bi-report/:id", "parent_page" => 1],
                    ["id" => 359, "group_dn_bn"=>"উপকারভোগীর সারসংক্ষেপ", "group_dn_en" => "Beneficiary Summary", "name" => "beneficiary-summary", "dn_bn" =>"উপকারভোগীর সারসংক্ষেপ", "dn_en" => "Beneficiary summary", "page_url" => "/reports/beneficiary-summery", "parent_page" => 1],
                    ["id" => 362, "group_dn_bn"=>"রিপোর্টিং তালিকা", "group_dn_en" => "Reporting List", "name" => "otp-dashboard", "dn_bn" =>"ওটিপি ড্যাসবোর্ড", "dn_en" => "OTP Dashboard", "page_url" => "/reports/otp-dashboard", "parent_page" => 1],
                ]
            ],

            /* -------------------------------------------------------------------------- */
            /*                              API MANAGER                              */
            /* -------------------------------------------------------------------------- */
            [
                'module_name' => $this->modulePermissionAPIManager,
                'sub_module_name' => $this->modulePermissionAPIManager,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 225, "group_dn_bn"=>"এপিআই ম্যানেজার ইউআরএল", "group_dn_en" => "API Manager URL", "name" => "url-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/api-manager/url-generate/create", "parent_page" => 1],
                    ["id" => 226, "group_dn_bn"=>"এপিআই ম্যানেজার ইউআরএল", "group_dn_en" => "API Manager URL", "name" => "url-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/api-manager/url-generate", "parent_page" => 1],
                    ["id" => 227, "group_dn_bn"=>"এপিআই ম্যানেজার ইউআরএল", "group_dn_en" => "API Manager URL", "name" => "url-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/api-manager/url-generate/edit/:id", "parent_page" => 1],
                    ["id" => 228, "group_dn_bn"=>"এপিআই ম্যানেজার ইউআরএল", "group_dn_en" => "API Manager URL", "name" => "url-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/api-manager/url-generate/delete", "parent_page" => 1],

                    ["id" => 229, "group_dn_bn"=>"এপিআই", "group_dn_en" => "API", "name" => "api-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/api-manager/api-generate/create", "parent_page" => 1],
                    ["id" => 230, "group_dn_bn"=>"এপিআই", "group_dn_en" => "API", "name" => "api-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/api-manager/api-generate", "parent_page" => 1],
                    ["id" => 231, "group_dn_bn"=>"এপিআই", "group_dn_en" => "API", "name" => "api-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/api-manager/api-generate/edit/:id", "parent_page" => 1],
                    ["id" => 232, "group_dn_bn"=>"এপিআই", "group_dn_en" => "API", "name" => "api-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/api-manager/api-generate/delete", "parent_page" => 1],

                    ["id" => 233, "group_dn_bn"=>"এপিআই ডেটা রিসিভ", "group_dn_en" => "API Data Receive", "name" => "apiDataReceive-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/api-manager/data-receiver/create", "parent_page" => 1],
                    ["id" => 234, "group_dn_bn"=>"এপিআই ডেটা রিসিভ", "group_dn_en" => "API Data Receive", "name" => "apiDataReceive-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/api-manager/data-receiver", "parent_page" => 1],
                    ["id" => 235, "group_dn_bn"=>"এপিআই ডেটা রিসিভ", "group_dn_en" => "API Data Receive", "name" => "apiDataReceive-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/api-manager/data-receiver/edit/:id", "parent_page" => 1],
                    ["id" => 236, "group_dn_bn"=>"এপিআই ডেটা রিসিভ", "group_dn_en" => "API Data Receive", "name" => "apiDataReceive-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/api-manager/data-receiver/delete", "parent_page" => 1],
                    ["id" => 350, "group_dn_bn"=>"এপিআই ডেটা রিসিভ", "group_dn_en" => "API Data Receive", "name" => "apiDataReceive-sendEmail", "dn_bn" =>"ইমেইল প্রেরণ", "dn_en" => "Send Email", "page_url" => "#", "parent_page" => 1],

                    ["id" => 245, "group_dn_bn"=>"এপিআই ড্যাশবোর্ড", "group_dn_en" => "API Dashboard", "name" => "apiDashboard-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/api-manager/dashboard", "parent_page" => 1],
                    ["id" => 246, "group_dn_bn"=>"এপিআই ড্যাশবোর্ড", "group_dn_en" => "API Dashboard", "name" => "apiDashboard-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/api-manager/dashboard", "parent_page" => 1],
                    ["id" => 247, "group_dn_bn"=>"এপিআই ড্যাশবোর্ড", "group_dn_en" => "API Dashboard", "name" => "apiDashboard-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/api-manager/dashboard", "parent_page" => 1],
                    ["id" => 248, "group_dn_bn"=>"এপিআই ড্যাশবোর্ড", "group_dn_en" => "API Dashboard", "name" => "apiDashboard-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/api-manager/dashboard", "parent_page" => 1],
                ]
            ],

            /* -------------------------------------------------------------------------- */
            /*                              SYSTEM AUDIT                              */
            /* -------------------------------------------------------------------------- */
            [
                'module_name' => $this->modulePermissionSystemAudit,
                'sub_module_name' => $this->modulePermissionSystemAudit,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 237, "group_dn_bn"=>"সিস্টেম অডিট ট্র্যাকিং", "group_dn_en" => "System Audit Tracking", "name" => "tracking-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/system-audit/information-tracking", "parent_page" => 1],
                    ["id" => 238, "group_dn_bn"=>"সিস্টেম অডিট ট্র্যাকিং", "group_dn_en" => "System Audit Tracking", "name" => "tracking-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/system-audit/information-tracking", "parent_page" => 1],
                    ["id" => 239, "group_dn_bn"=>"সিস্টেম অডিট ট্র্যাকিং", "group_dn_en" => "System Audit Tracking", "name" => "tracking-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/system-audit/information-tracking/edit/:id", "parent_page" => 1],
                    ["id" => 240, "group_dn_bn"=>"সিস্টেম অডিট ট্র্যাকিং", "group_dn_en" => "System Audit Tracking", "name" => "tracking-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/system-audit/information-tracking/delete", "parent_page" => 1],

                    ["id" => 241, "group_dn_bn"=>"অ্যাক্টিভিটি লগ", "group_dn_en" => "Activity Log", "name" => "activityLog-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/system-audit/activity-logs", "parent_page" => 1],
                    ["id" => 242, "group_dn_bn"=>"অ্যাক্টিভিটি লগ", "group_dn_en" => "Activity Log", "name" => "activityLog-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/system-audit/activity-logs", "parent_page" => 1],
                    ["id" => 243, "group_dn_bn"=>"অ্যাক্টিভিটি লগ", "group_dn_en" => "Activity Log", "name" => "activityLog-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/system-audit/activity-logs/edit/:id", "parent_page" => 1],
                    ["id" => 244, "group_dn_bn"=>"অ্যাক্টিভিটি লগ", "group_dn_en" => "Activity Log", "name" => "activityLog-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/system-audit/activity-logs/delete", "parent_page" => 1],

                ]
            ],

            /* -------------------------------------------------------------------------- */
            /*                             Training Management                            */
            /* -------------------------------------------------------------------------- */
            [
                'module_name' => $this->modulePermissionTrainingManagement,
                'sub_module_name' => $this->modulePermissionTrainingManagement,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 169, "group_dn_bn"=>"ট্রেইনিং", "group_dn_en" => "Training", "name" => "training-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/training-management/training/create", "parent_page" => 1],
                    ["id" => 170, "group_dn_bn"=>"ট্রেইনিং", "group_dn_en" => "Training", "name" => "training-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/training-management/training", "parent_page" => 1],
                    ["id" => 171, "group_dn_bn"=>"ট্রেইনিং", "group_dn_en" => "Training", "name" => "training-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/training-management/training/edit/:id", "parent_page" => 1],
                    ["id" => 172, "group_dn_bn"=>"ট্রেইনিং", "group_dn_en" => "Training", "name" => "training-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/training-management/training", "parent_page" => 1]
                ]

            ],
            [
                'module_name' => $this->modulePermissionTrainingManagement,
                'sub_module_name' => $this->modulePermissionTrainingManagement,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 249, "group_dn_bn"=>"প্রশিক্ষকের তথ্য", "group_dn_en" => "Trainer Information", "name" => "trainerInfo-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/training-management/trainer-information/create", "parent_page" => 1],
                    ["id" => 250, "group_dn_bn"=>"প্রশিক্ষকের তথ্য", "group_dn_en" => "Trainer Information", "name" => "trainerInfo-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/training-management/trainer-information", "parent_page" => 1],
                    ["id" => 251, "group_dn_bn"=>"প্রশিক্ষকের তথ্য", "group_dn_en" => "Trainer Information", "name" => "trainerInfo-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/training-management/trainer-information/edit/:id", "parent_page" => 1],
                    ["id" => 252, "group_dn_bn"=>"প্রশিক্ষকের তথ্য", "group_dn_en" => "Trainer Information", "name" => "trainerInfo-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/training-management/trainer-information/view/:id", "parent_page" => 1]
                ]

            ],
            [
                'module_name' => $this->modulePermissionTrainingManagement,
                'sub_module_name' => $this->modulePermissionTrainingManagement,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 254, "group_dn_bn"=>"প্রশিক্ষণ সার্কুলার", "group_dn_en" => "Training Circular", "name" => "trainingCircular-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/training-management/training-circular/create", "parent_page" => 1],
                    ["id" => 255, "group_dn_bn"=>"প্রশিক্ষণ সার্কুলার", "group_dn_en" => "Training Circular", "name" => "trainingCircular-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/training-management/training-circular", "parent_page" => 1],
                    ["id" => 256, "group_dn_bn"=>"প্রশিক্ষণ সার্কুলার", "group_dn_en" => "Training Circular", "name" => "trainingCircular-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/training-management/training-circular/edit/:id", "parent_page" => 1],
                    ["id" => 257, "group_dn_bn"=>"প্রশিক্ষণ সার্কুলার", "group_dn_en" => "Training Circular", "name" => "trainingCircular-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/training-management/training-circular/view/:id", "parent_page" => 1]
                ]

            ],
            [
                'module_name' => $this->modulePermissionTrainingManagement,
                'sub_module_name' => $this->modulePermissionTrainingManagement,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 258, "group_dn_bn"=>"টাইম স্লট", "group_dn_en" => "Time Slot", "name" => "timeStot-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/training-management/time-slots/create", "parent_page" => 1],
                    ["id" => 259, "group_dn_bn"=>"টাইম স্লট", "group_dn_en" => "Time Slot", "name" => "timeStot-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/training-management/time-slots", "parent_page" => 1],
                    ["id" => 260, "group_dn_bn"=>"টাইম স্লট", "group_dn_en" => "Time Slot", "name" => "timeStot-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/training-management/time-slots/edit/:id", "parent_page" => 1],
                    ["id" => 265, "group_dn_bn"=>"টাইম স্লট", "group_dn_en" => "Time Slot", "name" => "timeStot-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/training-management/time-slots/view/:id", "parent_page" => 1],
                    ["id" => 276, "group_dn_bn"=>"কোবো", "group_dn_en" => "Kobo", "name" => "kobo-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/training-management/kobo-token-update", "parent_page" => 1],
                ]

            ],
            [
                'module_name' => $this->modulePermissionTrainingManagement,
                'sub_module_name' => $this->modulePermissionTrainingManagement,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 266, "group_dn_bn"=>"প্রশিক্ষণ কর্মসূচী", "group_dn_en" => "Training Program", "name" => "trainingProgram-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/training-management/training-program/create", "parent_page" => 1],
                    ["id" => 267, "group_dn_bn"=>"প্রশিক্ষণ কর্মসূচী", "group_dn_en" => "Training Program", "name" => "trainingProgram-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/training-management/training-program", "parent_page" => 1],
                    ["id" => 268, "group_dn_bn"=>"প্রশিক্ষণ কর্মসূচী", "group_dn_en" => "Training Program", "name" => "trainingProgram-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/training-management/training-program/edit/:id", "parent_page" => 1],
                    ["id" => 269, "group_dn_bn"=>"প্রশিক্ষণ কর্মসূচী", "group_dn_en" => "Training Program", "name" => "trainingProgram-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/training-management/training-program/view/:id", "parent_page" => 1]
                ]

            ],
            [
                'module_name' => $this->modulePermissionTrainingManagement,
                'sub_module_name' => $this->modulePermissionTrainingManagement,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 270, "group_dn_bn"=>"প্রশিক্ষণ অংশগ্রহণকারী", "group_dn_en" => "Training Participant", "name" => "participant-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/training-management/participant/create", "parent_page" => 1],
                    ["id" => 271, "group_dn_bn"=>"প্রশিক্ষণ অংশগ্রহণকারী", "group_dn_en" => "Training Participant", "name" => "participant-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/training-management/participant", "parent_page" => 1],
                    ["id" => 272, "group_dn_bn"=>"প্রশিক্ষণ অংশগ্রহণকারী", "group_dn_en" => "Training Participant", "name" => "participant-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/training-management/participant/edit/:id", "parent_page" => 1],
                    ["id" => 273, "group_dn_bn"=>"প্রশিক্ষণ অংশগ্রহণকারী", "group_dn_en" => "Training Participant", "name" => "participant-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/training-management/participant/view/:id", "parent_page" => 1],

                    ["id" => 289, "group_dn_bn"=>"প্রশিক্ষণ ড্যাশবোর্ড", "group_dn_en" => "Training Dashboard", "name" => "trainingDashboard-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/training-management/dashboard", "parent_page" => 1],
                    ["id" => 290, "group_dn_bn"=>"প্রশিক্ষণ ড্যাশবোর্ড", "group_dn_en" => "Training Dashboard", "name" => "trainingDashboard-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/training-management/dashboard", "parent_page" => 1],
                ]

            ],

            [
                'module_name' => $this->modulePermissionSettingManagement,
                'sub_module_name' => $this->settingManagement,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 173, "group_dn_bn"=>"জেনেরাল সেটিংস", "group_dn_en" => "General Settings", "name" => "generalSetting-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/setting/general/create", "parent_page" => 0],
                    ["id" => 174, "group_dn_bn"=>"জেনেরাল সেটিংস", "group_dn_en" => "General Settings", "name" => "generalSetting-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/setting/general", "parent_page" => 0],
                    ["id" => 175, "group_dn_bn"=>"জেনেরাল সেটিংস", "group_dn_en" => "General Settings", "name" => "generalSetting-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "/setting/general/edit/:id", "parent_page" => 0],
                    ["id" => 176, "group_dn_bn"=>"জেনেরাল সেটিংস", "group_dn_en" => "General Settings", "name" => "generalSetting-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/setting/general", "parent_page" => 0],


                ]

            ],


            [
                'module_name' => $this->modulePermissionBeneficiaryManagement,
                'sub_module_name' => $this->subCommitteePermissionInformation,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 178, "group_dn_bn"=>"কমিটি অনুমতি", "group_dn_en" => "Committee Permission", "name" => "committee-permission-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/beneficiary-management/committee-permission/create", "parent_page" => 1],
                    ["id" => 179, "group_dn_bn"=>"কমিটি অনুমতি", "group_dn_en" => "Committee Permission", "name" => "committee-permission-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/beneficiary-management/committee-permission", "parent_page" => 1],
                    ["id" => 181, "group_dn_bn"=>"কমিটি অনুমতি", "group_dn_en" => "Committee Permission", "name" => "committee-permission-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/beneficiary-management/committee-permission", "parent_page" => 1]
                ]
            ],

            /* -------------------------------------------------------------------------- */
            /*                             Payroll Management again                        */
            /* -------------------------------------------------------------------------- */
            [
                'module_name' => $this->modulePermissionPayrollManagement,
                'sub_module_name' => $this->subPayrollSetting,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 261, "group_dn_bn"=>"পেরোল সেটিংস", "group_dn_en" => "Payroll Settings", "name" => "payroll-setting-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/payroll-management/payroll-setting", "parent_page" => 1],
                ]
            ],

            [
                'module_name' => $this->modulePermissionPayrollManagement,
                'sub_module_name' => $this->subPayrollVerificationSetting,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 262, "group_dn_bn"=>"পেরোল ভেরিফিকেশন সেটিংস", "group_dn_en" => "Payroll Verification Settings", "name" => "payroll-verification-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/payroll-management/payroll-verification-setting", "parent_page" => 1],
                ]
            ],
            [
                'module_name' => $this->modulePermissionPayrollManagement,
                'sub_module_name' => $this->subPayrollPaymentTracking,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 274, "group_dn_bn"=>"পেমেন্ট ট্র্যাকিং", "group_dn_en" => "Payment Tracking", "name" => "payroll-payment-tracking", "dn_bn" =>"ট্র্যাকিং", "dn_en" => "Tracking", "page_url" => "/payroll-management/payment-tracking", "parent_page" => 1],
                ]
            ],
            [
                'module_name' => $this->modulePermissionPayrollManagement,
                'sub_module_name' => $this->subPayrollDashboard,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 275, "group_dn_bn"=>"পেরোল ড্যাশবোর্ড", "group_dn_en" => "Payroll Dashboard", "name" => "payroll-dashboard-show", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/payroll-management/dashboard", "parent_page" => 1],
                ]
            ],
            [
                'module_name' => $this->modulePermissionEmergencyPayment,
                'sub_module_name' => $this->subEmergencyPayrollSetting,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 324, "group_dn_bn"=>"জরুরী পেরোল সেটিংস", "group_dn_en" => "Emergency Payroll Settings", "name" => "emergency-payroll-setting-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/emergency-payroll-management/payroll-setting", "parent_page" => 1],
                ]
            ],
            [
                'module_name' => $this->modulePermissionEmergencyPayment,
                'sub_module_name' => $this->subEmergencyPaymentDashboard,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 277, "group_dn_bn"=>"জরুরী পেমেন্ট ড্যাশবোর্ড", "group_dn_en" => "Emergency Payment Dashboard", "name" => "emergency-payment-dashboard-show", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/emergency-payment-management/dashboard", "parent_page" => 1],
                ]
            ],
            [
                'module_name' => $this->modulePermissionEmergencyPayment,
                'sub_module_name' => $this->subEmergencySupplementary,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 279, "group_dn_bn"=>"জরুরী সাপ্লিমেন্টারী পেরোল", "group_dn_en" => "Emergency Supplementary Payment ", "name" => "emergency-supplementary-payroll-show", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/emergency-payment/supplementary-payroll", "parent_page" => 1],
                    ["id" => 316, "group_dn_bn"=>"জরুরী সাপ্লিমেন্টারী পেরোল", "group_dn_en" => "Emergency Supplementary Payment ", "name" => "emergency-supplementary-cycle-details", "dn_bn" =>"বিস্তারিত", "dn_en" => "Details", "page_url" => "/emergency-payment/supplementary-payroll-show/:id", "parent_page" => 1],
                    ["id" => 310, "group_dn_bn"=>"জরুরী সাপ্লিমেন্টারী পেরোল", "group_dn_en" => "Emergency Supplementary Payment ", "name" => "emergency-supplementary-beneficiary-show", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/emergency-payment/supplementary-beneficiary/:id", "parent_page" => 1],
                ]
            ],
            [
                'module_name' => $this->modulePermissionEmergencyPayment,
                'sub_module_name' => $this->subEmergencySupplementary,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 317, "group_dn_bn"=>"জরুরী সাপ্লিমেন্টারী পেরোল", "group_dn_en" => "Emergency Supplementary Payment", "name" => "supplementary-payroll-show", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/payroll-management/supplementary-payroll", "parent_page" => 1],
                    ["id" => 318, "group_dn_bn"=>"জরুরী সাপ্লিমেন্টারী পেরোল", "group_dn_en" => "Emergency Supplementary Payment", "name" => "supplementary-cycle-details", "dn_bn" =>"বিস্তারিত", "dn_en" => "Details", "page_url" => "/payroll-management/supplementary-payroll-show/:id", "parent_page" => 1],
                    ["id" => 319, "group_dn_bn"=>"জরুরী সাপ্লিমেন্টারী পেরোল", "group_dn_en" => "Emergency Supplementary Payment", "name" => "supplementary-beneficiary-show", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/payroll-management/supplementary-beneficiary/:id", "parent_page" => 1],
                ]
            ],
            [
                'module_name' => $this->modulePermissionPayrollManagement,
                'sub_module_name' => $this->subPayrollPaymentCycle,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 309, "group_dn_bn"=>"পেরোল পেমেন্ট সাইকেল", "group_dn_en" => "Payroll Payment Cycle", "name" => "payroll-payment-cycle-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/payroll-management/payment-cycle", "parent_page" => 1],
                    ["id" => 320, "group_dn_bn"=>"পেরোল পেমেন্ট সাইকেল", "group_dn_en" => "Payroll Payment Cycle", "name" => "payroll-payment-cycle-show", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/payroll-payment/cycle/view/:id", "parent_page" => 1],
                    ["id" => 321, "group_dn_bn"=>"পেরোল পেমেন্ট সাইকেল", "group_dn_en" => "Payroll Payment Cycle", "name" => "payroll-payment-cycle-reject", "dn_bn" =>"বাতিল", "dn_en" => "Reject", "page_url" => "/payroll-payment/cycle/reject/:id", "parent_page" => 1],
                    ["id" => 322, "group_dn_bn"=>"পেরোল পেমেন্ট সাইকেল", "group_dn_en" => "Payroll Payment Cycle", "name" => "payroll-payment-cycle-send", "dn_bn" =>"প্রেরণ", "dn_en" => "Send", "page_url" => "/payroll-management/payment-cycle", "parent_page" => 1],
                ]
            ],
            [
                'module_name' => $this->modulePermissionPayrollManagement,
                'sub_module_name' => $this->subPayrollReconciliation,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 315, "group_dn_bn"=>"পেরোল রিকন্সিলিয়েশন সমন্বয়", "group_dn_en" => "Payroll Reconciliation (Coordination)", "name" => "payroll-reconciliation-cordination-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/payroll-management/reconciliation-cordination", "parent_page" => 1],
                    ["id" => 323, "group_dn_bn"=>"পেরোল রিকন্সিলিয়েশন সমন্বয়", "group_dn_en" => "Payroll Reconciliation (Coordination)", "name" => "payroll-reconciliation-cordination-edit", "dn_bn" =>"এডিট", "dn_en" => "Edit", "page_url" => "payroll-payment/reconciliation/edit/:id", "parent_page" => 1],
                    ["id" => 338, "group_dn_bn"=>"পেরোল রিকন্সিলিয়েশন সমন্বয়", "group_dn_en" => "Payroll Reconciliation (Coordination)", "name" => "payroll-reconciliation-cordination-update", "dn_bn" =>"আপডেট", "dn_en" => "Update", "page_url" => "/payroll-management/reconciliation-cordination", "parent_page" => 1],
                    ["id" => 339, "group_dn_bn"=>"পেরোল রিকন্সিলিয়েশন সমন্বয়", "group_dn_en" => "Payroll Reconciliation (Coordination)", "name" => "payroll-reconciliation-cordination-approve", "dn_bn" =>"অনুমোদন", "dn_en" => "Approve", "page_url" => "/payroll-management/reconciliation-cordination", "parent_page" => 1],
                    ["id" => 340, "group_dn_bn"=>"পেরোল রিকন্সিলিয়েশন সমন্বয়", "group_dn_en" => "Payroll Reconciliation (Coordination)", "name" => "payroll-reconciliation-cordination-delete", "dn_bn" =>"ডিলিট", "dn_en" => "Delete", "page_url" => "/payroll-management/reconciliation-cordination", "parent_page" => 1],
                ]
            ],


            /* -------------------------------------------------------------------------- */
            /*                             Data Migration                                 */
            /* -------------------------------------------------------------------------- */

            [
                'module_name' => $this->moduleDataMigration,
                'sub_module_name' => $this->moduleDataMigration,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 285, "group_dn_bn"=>"উপকারভোগী মাইগ্রেশন", "group_dn_en" => "Beneficiary Migration", "name" => "beneficiaryMigrration-create", "dn_bn" =>"ক্রিয়েট", "dn_en" => "Create", "page_url" => "/migration/beneficiary", "parent_page" => 1],
                    ["id" => 278, "group_dn_bn"=>"উপকারভোগী মাইগ্রেশন", "group_dn_en" => "Beneficiary Migration", "name" => "beneficiaryMigrration-view", "dn_bn" =>"ভিউ", "dn_en" => "View", "page_url" => "/migration/beneficiary", "parent_page" => 1],
                ]
            ],

            /* -------------------------------------------------------------------------- */
            /*                             Language Change                                  */
            /* -------------------------------------------------------------------------- */

            [
                'module_name' => $this->moduleLanguageChange,
                'sub_module_name' => $this->subLanguageChange,
                'guard_name' => $this->guard,
                'permissions' => [
                    ["id" => 342, "group_dn_bn"=>"মেনু সেটিংস", "group_dn_en" => "Menu Settings", "name" => "language-bn", "dn_bn" =>"বাংলা ভাষা", "dn_en" => "Bangali Language", "page_url" => "/system-configuration/languages/Bn", "parent_page" => 1],
                    ["id" => 343, "group_dn_bn"=>"মেনু সেটিংস", "group_dn_en" => "Menu Settings", "name" => "language-en", "dn_bn" =>"ইংরেজি ভাষা", "dn_en" => "English Language", "page_url" => "/system-configuration/languages/En", "parent_page" => 1],
                ]
            ],

        ];


        //last id or max_id = 363


        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('permissions')->truncate();
        for ($i = 0; $i < count($permissions); $i++) {
            $groupPermissions = $permissions[$i]['module_name'];
            $subModulePermissions = $permissions[$i]['sub_module_name'];
            $guardPermissions = $permissions[$i]['guard_name'];
            for ($j = 0; $j < count($permissions[$i]['permissions']); $j++) {
                //create permissions
                $permission = Permission::create([
                    'id' => $permissions[$i]['permissions'][$j]['id'],
                    'name' => $permissions[$i]['permissions'][$j]['name'],
                    'module_name' => $groupPermissions,
                    'sub_module_name' => $subModulePermissions,
                    'guard_name' => $guardPermissions,
                    'page_url' => $permissions[$i]['permissions'][$j]['page_url'],
                    'parent_page' => $permissions[$i]['permissions'][$j]['parent_page'],
                    'display_name_en' => $permissions[$i]['permissions'][$j]['dn_en'],
                    'display_name_bn' => $permissions[$i]['permissions'][$j]['dn_bn'],
                    'group_display_name_en' => $permissions[$i]['permissions'][$j]['group_dn_en'],
                    'group_display_name_bn' => $permissions[$i]['permissions'][$j]['group_dn_bn'],
                ]);
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
