<?php

namespace App\Support\Classes;

use Carbon\Carbon;

class Period
{
    /**
     * @var Carbon $from
     */
    public $from;

    /**
     * @var Carbon $to
     */
    public $to;

    /**
     * Create a new Period instance
     *
     * @param Carbon $from
     * @param Carbon $to
     *
     * @author Roy Freij <roy@bsbip.com>
     * @version 1.0.0
     */
    public function __construct(Carbon $from, Carbon $to)
    {
        $this->from = $from;
        $this->to = $to;
    }
}
