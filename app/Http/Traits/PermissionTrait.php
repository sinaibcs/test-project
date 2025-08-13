<?php

namespace App\Http\Traits;

use App\Models\User;

trait PermissionTrait
{
    use RoleTrait;

    //User ID
    protected $user_id;

    //permission groups
    private $permissionGroupAdminDashboard = 'AdminDashboard';
    private $permissionGroupAdminExpense = 'AdminExpense';
    private $permissionGroupAdminSupport = 'AdminSupport';
    private $permissionGroupAdminSetting = 'AdminSetting';

    // modules list
    private $modulePermissionSystemConfiguration = "SystemConfiguration";
    private $modulePermissionApplicationSelection = "ApplicationSelection";
    private $modulePermissionBeneficiaryManagement = "BeneficiaryManagement";
    private $modulePermissionPayrollManagement = "PayrollManagement";
    private $modulePermissionEmergencyPayment = "EmergencyPayment";
    private $modulePermissionGrievanceManagement = "GrievanceManagement";
    private $modulePermissionReportingSystem = "ReportingSystem";
    private $modulePermissionAPIManager = "APIManager";
    private $modulePermissionSystemAudit = "SystemAudit";
    private $modulePermissionTrainingManagement = "TrainingManagement";
    private $modulePermissionBudgetManagement = "BudgetManagement";
    private $modulePermissionAllotmentManagement = "AllotmentManagement";
    private $modulePermissionSettingManagement = "SettingManagement";

    // sub modules list

    // module 1 sub modules
    private $systemConfiguration = "system-configuration";

    private $subDemographicInformationManagement = "demographic-information-Management";
    private $subAllowanceProgramManagement = "Allowance-Program-Management";

    private $systemDashboard = "system-dashboard";
    private $subBanks = "bank-information";
    private $subMfs = "mfs-information";
    private $subBranch = "branch-information";
    private $subOfficeInformationManagement = "office-information-management";
    private $subFinancialInformationManagement = "financial-information-management";
    private $subUserManagement = "user-management";
    private $subRoleManagement = "role-management";
    private $subRolePermissionManagement = "role-permission-management";
    private $subDeviceRegistrationManagement = "device-registration-management";

    private $menuManagement = "menu-management";
    private $deviceRegistrationManagement = "device-registration";

    // module 2 sub modules
    private $subPovertyScoreManagement = "poverty-score-management";
    private $subOnlineApplicationManagement = "online-application-management";
    private $subBeneficiarySelectionManagement = "beneficiary-selection-management";


    // module 3 sub modules
    private $subCommitteeInformation = "committee-information";
    private $subCommitteePermissionInformation = "committee-permission-information";
    private $subAllocationInformation = "allocation-information";
    private $subBeneficiaryInformationManagement = "beneficiary-information-management";
    private $subBeneficiaryReplacement = "beneficiary-replacement";
    private $subBeneficiaryIDCard = "beneficiary-id-card";
    private $subBeneficiaryIDShifting = "beneficiary-id-shifting";
    private $subBeneficiaryIDExit = "beneficiary-id-exit";


    // module 4 sub modules
    private $subPaymentProcessorInformation = "payment-processor-information";
    private $subPayrollCreate = "payroll-create";
    private $subPayrollList = "payroll-list";
    private $subPayrollReconciliationDataPull = "payroll-reconciliation-data-pull";
    private $subEmergencyPayrollReconciliationDataPull = "Emergency-payroll-reconciliation-data-pull";
    private $subEmergencyAllotment = "emergency-allotment";
    private $subEmergencyBeneficiary = "emergency-beneficiary";
    private $subEmergencyPaymentCycle = "emergency-payment-cycle";
    private $subEmergencyPaymentReconciliation = "emergency-payment-reconciliation";
    private $subManageEmergencyBeneficiary = "manage-emergency-beneficiary";
    private $subEmergencyPayorll = "emergency-payroll";
    private $subEmergencyPayorllReconciliationDataPull = "emergency-payroll-reconciliation-data-pull";
    private $subPayrollSetting = "payroll-setting";
    private $subPayrollVerificationSetting = "payroll-verification-setting";
    private $subPayrollPaymentTracking = "payment-tracking";
    private $subPaymentProcessor = "payment-processor";
    private $subPayrollDashboard = "payroll-dashboard";
    private $subEmergencyPaymentDashboard = "emergency-payment-dashboard";
    private $subEmergencyPayrollSetting = "emergency-payroll-setting";
    private $subEmergencySupplementary = "emergency-supplementary";
    private $subPayrollSupplementary = "payroll-supplementary";
    private $subPayrollPaymentCycle = "Payroll-Payment-cycle";
    private $subPayrollReconciliation = "Payroll-Reconciliation";
    private $subLanguageChange = "Language-change";


    // module 5 sub modules
    private $subGrievanceSetting = "grievance-setting";
    private $subGrievanceList = "grievance-list";
    private $subGrievanceDashboard = "grievance-dashboard";
    private $subGrievanceType = "grievance-type";
    private $subGrievanceSubject = "grievance-subject";


    // module 6 sub modules

    private $budgetManagement = "budget-management";
    private $allotmentManagement = "allotment-management";
    private $settingManagement = "setting-management";

    //data migration
    private $moduleDataMigration = "dataMigration";

    // language change
    private $moduleLanguageChange = "language-change-module";


    public function getUserPermissions()
    {
        if (request()->has('user_id')) {

            $this->user_id = request()->user_id;

            $user = User::withoutGlobalScope('assign_location_type')->findOrFail($this->user_id);
            // echo $user->office->office_type;
            // echo $user->assign_location->type;

            if ($user->user_type == $this->superAdminId) {
                return false;
            }

            if ($user->user_type == $this->staffId) {

                // if user Has Office
                $officeHead = User::withoutGlobalScope('assign_location_type')->where('office_id', $user->office_id)->whereHas('roles', function ($query) {
                    $query->where('name', $this->officeHead);
                })->first();

                // print_r($officeHead);
                if ($officeHead) {
                    // IS OFFICE HEAD
                    $data = array(
                        'type' => $officeHead->assign_location->type,
                        'location_id' => $officeHead->assign_location->id,
                    );
                    return $data;
                    // return $officeHead->assign_location->type; // Office Head
                } else {
                    // NOT OFFICE HEAD
                    $data = array(
                        'type' => $officeHead->assign_location->type,
                        'location_id' => $officeHead->assign_location->id,
                    );
                    // return $user->assign_location->type; // Office Staff
                    return $data;
                }
            }
        }
    }

    public function getUserPermissionsForUser()
    {

        // $data = array(
        //     'type' => 'division',
        //     'location_id' => '6',
        //     'user_id' => request()->user_id,
        // );
        // return $data;

        if (request()->has('user_id')) {

            $this->user_id = request()->user_id;
            $user = User::withoutGlobalScope('assign_location_type')->findOrFail($this->user_id);

            // $user = DB::table('users')->where('id', $this->user_id)->first();
            // echo $user->office->office_type;
            // echo $user->assign_location->type;

            if ($user->user_type == $this->superAdminId) {
                return false;
            }


            if ($user->user_type == $this->staffId) {
                $officeHead = User::withoutGlobalScope('assign_location_type')->where('office_id', $user->office_id)->whereHas('roles', function ($query) {
                    $query->where('name', $this->officeHead);
                })->first();

                // print_r($officeHead);
                if ($officeHead) {
                    // IS OFFICE HEAD
                    $data = array(
                        'type' => $officeHead->assign_location->type,
                        'location_id' => $officeHead->assign_location->id,
                    );
                    return $data;
                    // return $officeHead->assign_location->type; // Office Head
                } else {
                    // NOT OFFICE HEAD
                    $data = array(
                        'type' => $officeHead->assign_location->type,
                        'location_id' => $officeHead->assign_location->id,
                    );
                    // return $user->assign_location->type; // Office Staff
                    return $data;
                }

                // $officeHead = DB::table('model_has_roles')
                // ->leftJoin('users', 'model_has_roles.model_id', '=', 'users.id')
                // ->leftJoin('offices', 'users.office_id', '=', 'offices.id')
                // ->leftJoin('locations', 'offices.assign_location_id', '=', 'locations.id')
                // ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
                // ->select(
                //     'locations.type as type',
                //     'locations.id as location_id',
                // )
                // ->where('users.id', $this->user_id)
                // ->where('roles.name', $this->officeHead)
                // ->first();

                // // print_r($officeHead);
                // if ($officeHead) {
                //     // IS OFFICE HEAD
                //     $data = array(
                //         'type' => $officeHead->type,
                //         'location_id' => $officeHead->location_id,
                //     );
                //     return $data;
                // } else {
                //     // NOT OFFICE HEAD
                //     $officeHead = DB::table('model_has_roles')
                //     ->leftJoin('users', 'model_has_roles.model_id', '=', 'users.id')
                //     ->leftJoin('offices', 'users.office_id', '=', 'offices.id')
                //     ->leftJoin('locations', 'offices.assign_location_id', '=', 'locations.id')
                //     ->select(
                //         'locations.type as type',
                //         'locations.id as location_id',
                //     )
                //     ->where('users.id', $this->user_id)
                //     ->first();

                //     $data = array(
                //         'type' => $officeHead->type,
                //         'location_id' => $officeHead->location_id,
                //     );
                //     return $data;
                // }

                // $data = array(
                //     'type' => 'division',
                //     'location_id' => '6',
                // );
                // return $data;
            }
        }
    }
}