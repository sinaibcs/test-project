<?php

namespace App\Http\Traits;

trait UserTrait
{
    //approve/pending
    private $userAccountDeactivate = 0;
    private $userAccountApproved = 1;
    private $userAccountBanned = 2;
    private $userAccountRejected = 3;
    private $userAccountPending = 4;
    private $userAccountInactive = 5;
    //user online status
    private $userOnline = 1;
    private $userOffline = 0;

    // user types
    private $superAdminUserType = 1;
    private $staffType = 2;


    // wallet type
    private $walletTypeBkash = 1;
    private $walletTypeRocket = 2;
    private $walletTypeNagad = 3;





}
