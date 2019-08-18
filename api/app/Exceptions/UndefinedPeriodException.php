<?php

namespace App\Exceptions;

use Exception;

class UndefinedPeriodException extends Exception
{
    /**
     * Create a new UndefinedPeriodException instance
     *
     * @param string $period
     *
     * @author Roy Freij <roy@bsbip.com>
     * @version 1.0.0
     */
    public function __construct(string $period)
    {
        $this->message = trans('exception.undefined_period', [
            'period' => $period,
        ]);
    }
}
