<?php

namespace App\Support;

use App\Exceptions\UndefinedPeriodException;
use App\Support\Classes\Period;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PeriodSupport
{
    /**
     * Dynamically retrieve period
     *
     * @param string $period
     *
     * @return Period
     *
     * @throws UndefinedPeriodException
     *
     * @author Roy Freij <roy@bsbip.com>
     * @version 1.0.0
     */
    public static function retrieve(string $period): Period
    {
        $period = Str::camel($period);

        if (method_exists(self::class, $period)) {
            return self::$period();
        }

        throw new UndefinedPeriodException($period);
    }

    /**
     * Retrieve period for current month
     *
     * @return Period
     *
     * @author Roy Freij <roy@bsbip.com>
     * @version 1.0.0
     */
    public static function currentMonth(): Period
    {
        return new Period(
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        );
    }

    /**
     * Retrieve period for current week
     *
     * @return Period
     *
     * @author Roy Freij <roy@bsbip.com>
     * @version 1.0.0
     */
    public static function currentWeek(): Period
    {
        return new Period(
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        );
    }

    /**
     * Retrieve period for today
     *
     * @return Period
     *
     * @author Roy Freij <roy@bsbip.com>
     * @version 1.0.0
     */
    public static function today(): Period
    {
        return new Period(
            Carbon::now()->startOfDay(),
            Carbon::now()->endOfDay()
        );
    }

    /**
     * Retrieve period for yesterday
     *
     * @return Period
     *
     * @author Roy Freij <roy@bsbip.com>
     * @version 1.0.0
     */
    public static function yesterday(): Period
    {
        return new Period(
            Carbon::now()->yesterday()->startOfDay(),
            Carbon::now()->yesterday()->endOfDay()
        );
    }

    /**
     * Retrieve period for the last seven days
     *
     * @return Period
     *
     * @author Roy Freij <roy@bsbip.com>
     * @version 1.0.0
     */
    public static function lastSevenDays(): Period
    {
        return new Period(
            Carbon::now()->subDays(Carbon::DAYS_PER_WEEK),
            Carbon::now()
        );
    }

    /**
     * Retrieve period for the last thirty days
     *
     * @return Period
     *
     * @author Roy Freij <roy@bsbip.com>
     * @version 1.0.0
     */
    public static function lastThirtyDays(): Period
    {
        return new Period(
            Carbon::now()->subDays(30),
            Carbon::now()
        );
    }
}
