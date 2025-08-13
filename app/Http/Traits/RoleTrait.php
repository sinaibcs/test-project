<?php

namespace App\Http\Traits;

trait RoleTrait
{
    //role list
    private $superAdminId = 1;
    private $staffId = 2;



    //*------------------------------------ Super Admin Roles ------------------------------------*/

    private $superAdmin = 'super-admin';
    private $staff = 'staff';
    //*------------------------------------ Office Roles ------------------------------------*/

    private $officeHead = 'office-head';
    private $dataEntryOperator = 'data-entry-operator';
    private $applicationListRole = 'application-list-role';


    //*------------------------------------ Committee Type Roles ------------------------------------*/

    private $committee = 'committee';
    private $trainer = 'trainer';
    private $participant = 'participant';

    private $unionCommittee = 'union-committee';
    private $upazilaCommittee = 'upazila-committee';

    private $wardCommittee = 'ward-committee';
    private $cityCorporationCommittee = 'city-corporation-committee';
    private $circleSocialCommittee = 'circle-social-committee';
    private $pouroCommittee = 'pouro-committee';
    private $divisionCommittee = 'division-committee';
    private $districtCommittee = 'district-committee';

    // if the role is super admin has all permissions
    // if the role is officeHead has all permissions assigned by super admin

    // Ministry
    // Head Office
    // Circle Social Office


    // ****************User Management********************************
    // Super Admin Role
    // Super Admin Can Approve the Created User and Make them Active or Inactive
    // ************************************************
    // Approve | Reject | Forward | Return
    // ************************************************
    //    0    |    1   |     1   |    0
    // ************************************************

    // Users with " Office Type = Division Office"
    //  will have the permission to approve the user with
    //  "Office Type = District Office",
    //  "Office Type = Upazila Office",
    //  "Office Type = Union Office",
    //  "Office Type = City Corporation Office" and make them active or inactive
    // ************************************************
    // Approve | Reject | Forward | Return
    // ************************************************
    //    0    |    0   |     0   |    0
    // ************************************************

    // Users with " Office Type = District Office"
    //  will have the permission to approve the user with
    //  "Office Type = Upazila Office",
    //  "Office Type = Union Office",
    //  "Office Type = City Corporation Office" and make them active or inactive
    // ************************************************
    // Approve | Reject | Forward | Return
    // ************************************************
    //    0    |    0   |     0   |    0
    // ************************************************

    //




    // Office Head - Application Selection Module
    // Users who belong to office and is office head can see all the applications under that office
    // ************************************************
    // Approve | Reject | Forward | Return
    // ************************************************
    //    0    |    1   |     1   |    0
    // ************************************************

    // Application List Role - Application Selection Module
    // Users who belong to Union Committee Type and has Application List Role can see all the applications under that office location has following permission
    // ************************************************
    // Approve | Reject | Forward | Return
    // ************************************************
    //    0    |    1   |     1   |    0
    // ************************************************

    // Application List Role - Application Selection Module
    // Users who belong to Upazila Committee Type and has Application List Role can see all the applications under that office has following permission
    // ************************************************
    // Approve | Reject | Forward | Return
    // ************************************************
    //    1    |    1   |     1   |    1
    // ************************************************


}
