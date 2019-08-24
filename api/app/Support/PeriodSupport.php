<?php

namespace App\Support;

use App\Exceptions\UndefinedPeriodException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use stdClass;

class PeriodSupport
{
    /**
     * Dynamically retrieve period
     *
     * @param string $period
     *
     * @return stdClass
     *
     * @throws UndefinedPeriodException
     *
     * @author Roy Freij <roy@bsbip.com>
     * @version 1.0.0
     */
    public static function retrieve(string $period): stdClass
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
     * @return stdClass
     *
     * @author Roy Freij <roy@bsbip.com>
     * @version 1.0.0
     */
    public static function currentMonth(): stdClass
    {
        return (object) [
            'from' => Carbon::now()->startOfMonth(),
            'to' => Carbon::now()->endOfMonth(),
        ];
    }

    /**
     * Retrieve period for current week
     *
     * @return stdClass
     *
     * @author Roy Freij <roy@bsbip.com>
     * @version 1.0.0
     */
    public static function currentWeek(): stdClass
    {
        return (object) [
            'from' => Carbon::now()->startOfWeek(),
            'to' => Carbon::now()->endOfWeek(),
        ];
    }

    /**
     * Retrieve period for today
     *
     * @return stdClass
     *
     * @author Roy Freij <roy@bsbip.com>
     * @version 1.0.0
     */
    public static function today(): stdClass
    {
        return (object) [
            'from' => Carbon::now()->startOfDay(),
            'to' => Carbon::now()->endOfDay(),
        ];
    }

    /**
     * Retrieve period for yesterday
     *
     * @return stdClass
     *
     * @author Roy Freij <roy@bsbip.com>
     * @version 1.0.0
     */
    public static function yesterday(): stdClass
    {
        return (object) [
            'from' => Carbon::now()->yesterday()->startOfDay(),
            'to' => Carbon::now()->yesterday()->endOfDay(),
        ];
    }

    /**
     * Retrieve period for the last seven days
     *
     * @return stdClass
     *
     * @author Roy Freij <roy@bsbip.com>
     * @version 1.0.0
     */
    public static function lastSevenDays(): stdClass
    {
        return (object) [
            'from' => Carbon::now()->subDays(Carbon::DAYS_PER_WEEK),
            'to' => Carbon::now(),
        ];
    }

    /**
     * Retrieve period for the last thirty days
     *
     * @return StdClass
     *
     * @author Roy Freij <roy@bsbip.com>
     * @version 1.0.0
     */
    public static function lastThirtyDays(): StdClass
    {
        return (object) [
            'from' => Carbon::now()->subDays(30),
            'to' => Carbon::now(),
        ];
    }
}
