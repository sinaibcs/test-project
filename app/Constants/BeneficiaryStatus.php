<?php

namespace App\Constants;


class BeneficiaryStatus
{
    public const ACTIVE = 1;
    public const INACTIVE = 2;
    public const WAITING = 3;

    public const ALL = [
        self::ACTIVE => 'Forward',
        self::INACTIVE => 'Approve',
        self::WAITING => 'Waiting'
    ];


}
