<?php

namespace Codebright\Rental\Http\Helpers;

class PermissionList {
    public const ADD_ELECTRICITY_BILL = "rental.electricity-bill.add";

    public static function getConstants()
    {
        return [
            9001 => self::ADD_ELECTRICITY_BILL,
        ];
    }
}