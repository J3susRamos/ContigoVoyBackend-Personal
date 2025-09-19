<?php

namespace App\Traits;

use DateTime;
use DateTimeZone;
use Exception;

trait DateHelper
{
    /**
     * @throws Exception
     */
    public static function formatDateToCustomString(): string
    {
        $date = new DateTime('now', new DateTimeZone('America/Lima'));

        return $date->format('D M d Y H:i:s \G\M\TO (T)');
    }
}
