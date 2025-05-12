<?php

namespace App\Enums;

enum CostComponentType: string
{
    case Mandatory = 'Mandatory';
    case NonMandatory = 'Non Mandatory';
    case Allowance = 'Allowance';
    case AllowanceOffice = 'Allowance Office';
}
