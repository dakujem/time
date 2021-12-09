<?php

declare(strict_types=1);

namespace Dakujem;

use Carbon\Carbon;
use DateTime;
use RuntimeException;

/**
 * TimeHelper.
 *
 * Note:    only time formats defined in constants are supported officially,
 *            however, the use of custom formats is possible.
 *            Avoiding funny formats is advisable though.
 *            Known not to work (parsing): "?i:s"
 *
 *
 * @author Andrej Rypak (dakujem) <xrypak@gmail.com>
 */
class TimeHelper
{
    //
    // format consts
    //
    const FORMAT_HMS = '?H:i:s'; //           02:34:56     or  -123:45:59
    const FORMAT_HM = '?H:i'; //              02:34        or  -123:45
    const FORMAT_HMS_SIGNED = '+H:i:s'; //   +02:34:56     or  -123:45:59
    const FORMAT_HM_SIGNED = '+H:i'; //      +02:34        or  -123:45
    const FORMAT_HMSA = 'h:i:s A'; //         12:34:56 AM  or    01:23:45 PM
    const FORMAT_HMA = 'h:i A'; //            12:34 AM     or    01:23 PM

    /**
     * Default format (static).
     * @var string
     */
    public static $defaultFormat = self::FORMAT_HMS;

    /**
     * Format the time using the format provided.
     *
     * The Time::FORMAT_* constants can be used to format the time to most used time formats.
     * Recognized characters for format are "?+HhisGgAa".
     * - see php date() function for "HhisGgAa",
     * - '?' is used for the minus sign, only present when the time is negative
     * - '+' is used for minus and plus sign, always present
     *
     *
     * @param TimeInterface $time
     * @param string|NULL $format
     * @return string formatted time
     */
    public static function format(TimeInterface $time, $format = null)
    {
        $source = $format !== null && $format !== '' ? $format : self::$defaultFormat;
        $neg = $time->isNegative();
        $v = $time->isValidDayTime();
        $h = $time->getHours();
        $h12 = $v ? ($h % 12 === 0 ? 12 : $h % 12) : $h;
        $m = $time->getMinutes();
        $s = $time->getSeconds();
        return str_replace(['?', '+', 'H', 'h', 'G', 'g', 'i', 's', 'A', 'a'], [
            $neg ? '-' : '', // ?
            $neg ? '-' : '+', // +
            sprintf('%02d', $h), // H
            sprintf('%02d', $h12), // h
            $h, // G
            $h12, // g
            sprintf('%02d', $m), // i
            sprintf('%02d', $s), // s
            $v ? ($h < 12 ? 'AM' : 'PM') : '', // A
            $v ? ($h < 12 ? 'am' : 'pm') : '', // a
        ], $source);
    }

    /**
     * Returns the time in seconds or NULL.
     *
     *
     * @param mixed $time
     * @param string|NULL $format
     * @return int|NULL returns NULL when the time passed is NULL or an empty string
     * @throws RuntimeException
     */
    public static function parse($time, $format = null)
    {
        if (is_numeric($time)) {
            // regard it as seconds
            return $time;
        } elseif ($time === null || $time === '') {
            return null;
        } elseif (is_string($time)) {
            // regard it as time string
            return self::parseFormat($time, $format === null ? self::$defaultFormat : $format);
        } elseif (is_array($time)) {
            // [s, m, h, d, w]
            $s = reset($time);
            $m = next($time);
            $h = next($time);
            $d = next($time);
            $w = next($time);
            return empty($s) ? null : self::calculateSeconds($w, $d, $h, $m, $s);
        } elseif ($time instanceof TimeInterface) {
            return $time->toSeconds();
        } elseif ($time instanceof Carbon) {
            return self::parse([$time->second, $time->minute, $time->hour]);
        } elseif ($time instanceof DateTime) {
            return self::parse($time->format('H:i:s'));
        }
        throw new RuntimeException('Invalid argument passed.');
    }

    /**
     * @note: sign handling should be improved
     */
    private static function parseFormat($value, $format)
    {
        // 1/ -----------------------------------------------------------------------------------------
        // read the numbers form the input string

        $numbers = null;
        if (!preg_match('#([+-]?[0-9]+)(.([+-]?[0-9]+)?(.([+-]?[0-9]+)?)?)?#', $value, $numbers)) { // PREG_OFFSET_CAPTURE
            return null;
        }
        $hi = 1;
        $mi = 3;
        $si = 5;
        // $vals contain the first, second and third number found in the $value string
        $vals = [
            isset($numbers[$hi]) ? $numbers[$hi] : 0,
            isset($numbers[$mi]) ? $numbers[$mi] : 0,
            isset($numbers[$si]) ? $numbers[$si] : 0,
        ];

        // 2/ -----------------------------------------------------------------------------------------
        // according to the format string, decide which numbers denote hours, minutes and seconds

        $hpos1 = stripos($format, 'h');
        $hpos = $hpos1 !== false ? $hpos1 : stripos($format, 'g');
        $ipos = stripos($format, 'i');
        $spos = stripos($format, 's');
        // hpos, ipos, spos contain the position in $format
        if ($hpos === false && $ipos === false && $spos === false) {
            return null;
        }
        // $keys contain valid references to hours, minutes and seconds
        $h = $m = $s = 0;
        $keys = [];
        if ($hpos !== false) {
            $keys[$hpos] = &$h; // reference to hours
        }
        if ($ipos !== false) {
            $keys[$ipos] = &$m; // reference to minutes
        }
        if ($spos !== false) {
            $keys[$spos] = &$s; // reference to seconds
        }
        ksort($keys); // sort $keys according to occurence in $format
        foreach ($keys as &$ref) {
            // match the references in $keys with the values
            $ref = current($vals); // $vals contain values read from $value string
            next($vals);
        }

        // 3/ -----------------------------------------------------------------------------------------
        // correct negative values

        $hneg = substr((string)$h, 0, 1) === '-'; // hours negative
        $mneg = substr((string)$m, 0, 1) === '-'; // minutes negative
        $sneg = substr((string)$s, 0, 1) === '-'; // seconds negative
        if (true) {
            $h = (int)$h;
            $m = (int)$m;
            $s = (int)$s;
        }
        if (substr_count($format, '?') <= 1 && substr_count($format, '+') <= 1) {
            // this corrects the reading of times like -12:30, when format contains only one sign,
            // consequently, -12:30 will result in -12 hours and -30 minutes time, when format is ?H:i
            // when format is set as ?H:?i, this will not happen, and will result in time -11 hours and 30 minutes (-12 hours +30 minutes))
            if ($hneg) {
                $m = $mneg ? $m : -$m;
                $s = $sneg ? $s : -$s;
            } else {
                $m = !$mneg ? $m : -$m;
                $s = !$sneg ? $s : -$s;
            }
            // NOTE: this does not cover the case when format "?i:s" is used
        }

        // 4/ -----------------------------------------------------------------------------------------
        // check for and correct the 12-hour format (if used)

        $f12h = stripos($format, 'a') !== false; // check for 12-h format?
        if ($f12h) {
            if ($h > 12 || $h < 0) { // invalid 12h format
                return null;
            }
            $a = stripos($value, 'am');
            if ($a === false) {
                $p = stripos($value, 'pm');
                if ($p !== false) {
                    $am = false;
                } else {
                    return null; // AM or PM not found
                }
            } else {
                $am = true;
            }
            // now $am contains am/pm, correct the time
            if ($h == 12 && $am) {
                $h = 0;
            } elseif ($h != 12 && !$am) {
                $h = $h + 12; // PM
            }
        }

        // 5/ -----------------------------------------------------------------------------------------
        // return the result

        return self::calculateSeconds(0, 0, $h, $m, $s);
    }

    private static function calculateSeconds($weeks, $days, $hours, $minutes, $seconds)
    {
        return
            (is_numeric($weeks) ? $weeks : (int)$weeks) * TimeInterface::WEEK +
            (is_numeric($days) ? $days : (int)$days) * TimeInterface::DAY +
            (is_numeric($hours) ? $hours : (int)$hours) * TimeInterface::HOUR +
            (is_numeric($minutes) ? $minutes : (int)$minutes) * TimeInterface::MINUTE +
            (is_numeric($seconds) ? $seconds : (int)$seconds) * TimeInterface::SECOND;
    }
}
