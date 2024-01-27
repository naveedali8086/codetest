<?php

namespace App\Enums;

enum JobType
{
case RWS;
case UNPAID;
case PAID;

    public function getJobType($customerType)
    {

    }

}
