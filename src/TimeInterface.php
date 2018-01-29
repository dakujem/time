<?php


namespace Dakujem;

use Carbon\Carbon,
	DateTime;


/**
 * Time object interface.
 *
 *
 * @author Andrej Rypak (dakujem) <xrypak@gmail.com>
 */
interface TimeInterface
{
	//
	// time constants
	//
	const SECOND = 1;
	const MINUTE = 60;
	const HOUR = 3600; //          60 * 60
	const DAY = 86400; //          60 * 60 * 24
	const WEEK = 604800; //        60 * 60 * 24 * 7
	const HOUR_MINUTES = 60;
	const DAY_MINUTES = 1440; //        60 * 24
	const WEEK_MINUTES = 10080; // 60 * 60 * 24
	const DAY_HOURS = 24;
	const WEEK_HOURS = 168; //               24 * 7
	const WEEK_DAYS = 7;

	/**/


	/**
	 * Add given time value:
	 * $this + $time
	 *
	 *
	 * @param int|string|static|DateTime|Carbon $time any parsable time format
	 * @return static
	 */
	function add($time);


	/**
	 * Substract given time value:
	 * $this - $time
	 *
	 *
	 * @param int|string|static|DateTime|Carbon $time any parsable time format
	 * @return static
	 */
	function sub($time);


	/**
	 * Multiply the time by $x:
	 * $this * $x
	 *
	 *
	 * @param int|double $x
	 * @return static
	 */
	function mult($x);


	/**
	 * Divide the time by $x:
	 * $this / $x
	 *
	 * Note: division by zero results in INF.
	 *
	 * @param int|double $x
	 * @return int|double
	 */
	function div($x);


	/**
	 * Modulate the time by $x:
	 * $this % $x
	 *
	 * Note: division by zero results in NAN.
	 *
	 * @param int|double $x
	 * @return int|double
	 */
	function mod($x);


	/**
	 * Perform a less-than comparison:
	 * $this < $time
	 *
	 *
	 * @param int|string|static|DateTime|Carbon $time any parsable time format
	 * @return bool
	 */
	function lt($time);


	/**
	 * Perform a less-than-or-equal comparison:
	 * $this <= $time
	 *
	 *
	 * @param int|string|static|DateTime|Carbon $time any parsable time format
	 * @return bool
	 */
	function lte($time);


	/**
	 * Perform a greater-than comparison:
	 * $this > $time
	 *
	 *
	 * @param int|string|static|DateTime|Carbon $time any parsable time format
	 * @return bool
	 */
	function gt($time);


	/**
	 * Perform a greater-than-or-equal comparison:
	 * $this >= $time
	 *
	 *
	 * @param int|string|static|DateTime|Carbon $time any parsable time format
	 * @return bool
	 */
	function gte($time);


	/**
	 * Perform a equal-to comparison:
	 * $this == $time
	 *
	 *
	 * @param int|string|static|DateTime|Carbon $time any parsable time format
	 * @return bool
	 */
	function eq($time);


	/**
	 * Perform a not-equal-to comparison:
	 * $this != $time
	 *
	 *
	 * @param int|string|static|DateTime|Carbon $time any parsable time format
	 * @return bool
	 */
	function neq($time);


	/**
	 * Return TRUE when the time is within the interval defined by the given times.
	 *
	 * Performs one of these comparisons:
	 *   $timeMin <= $this <= $timeMax    // this comparison is done for $sharp==FALSE (the default)
	 *   $timeMin <  $this <  $timeMax    // this comparison is done for $sharp==TRUE
	 *
	 * Note: $timeMin and $timeMax are determined from $time1 and $time2.
	 *
	 *
	 * @param int|string|static|DateTime|Carbon $time1 any parsable time format
	 * @param int|string|static|DateTime|Carbon $time2 any parsable time format
	 * @param bool $sharp [=FALSE] exclude the extremes of the interval?
	 * @return bool
	 */
	function between($time1, $time2, $sharp = FALSE);


	/**
	 * Is the time a valid day time?
	 * e.g. Is the time between 00:00:00 and 23:59:59 ?
	 *
	 *
	 * @return bool
	 */
	function isValidDayTime();


	/**
	 * Clip the time to a valid day time between 00:00:00 and 23:59:59.
	 * This will perform a modulo-DAY operation: $this % DAY
	 *
	 *
	 * @return static containing time between 00:00:00 and 23:59:59
	 */
	function clipToDayTime();


	function addSeconds($seconds = 1);


	function addMinutes($minutes = 1);


	function addHours($hours = 1);


	function addDays($days = 1);


	function addWeeks($weeks = 1);


	function subSeconds($seconds = 1);


	function subMinutes($minutes = 1);


	function subHours($hours = 1);


	function subDays($days = 1);


	function subWeeks($weeks = 1);


	/**
	 * Indicate whether the time is equal to zero.
	 * @see isNULL()
	 *
	 *
	 * @return bool
	 */
	function isZero();


	/**
	 * Indicate whether the time is NULL,
	 * which means the time was not set or has been reset to NULL.
	 * @see isZero()
	 *
	 *
	 * @return bool
	 */
	function isNULL();


	/**
	 * Indicate whether the time is negative or not.
	 *
	 *
	 * @return bool TRUE for any negative value, FALSE for positive and zero time
	 */
	function isNegative();


	/**
	 * Returns -1 when the time is negative, 0 when it is zero, +1 when it is positive.
	 *
	 *
	 * @return int
	 */
	function getSignum();


	/**
	 * Return the hour part of the time.
	 * WARNING: this does not return the time converted to hours! For that purpose, use the toHours() method.
	 *
	 *  HH:MM:SS
	 *  \/
	 *
	 * @return int
	 */
	function getHours();


	/**
	 * Return the minute part of the time.
	 * WARNING: this does not return the time converted to minutes! For that purpose, use the toMinutes() method.
	 *
	 *  HH:MM:SS
	 *     \/
	 *
	 * @return int
	 */
	function getMinutes();


	/**
	 * Return the seconds part of the time.
	 * WARNING: this does not return the time converted to seconds! For that purpose, use the toSeconds() method.
	 *
	 *  HH:MM:SS.frac
	 *        \/
	 *
	 * @return int
	 */
	public function getSeconds();


	/**
	 * Return the remaining fraction of a second. Returns NULL when the time value is integer.
	 *
	 *  HH:MM:SS.frac
	 *           \__/
	 *
	 * @return double|NULL
	 */
	public function getSecondFraction();


	/**
	 * Return the stored time in seconds.
	 *
	 *
	 * @return int|double
	 */
	function toSeconds();


	/**
	 * Return the stored time in minutes.
	 *
	 *
	 * @return int|double
	 */
	function toMinutes();


	/**
	 * Return the stored time in hours.
	 *
	 *
	 * @return int|double
	 */
	function toHours();


	/**
	 * Return the stored time in days.
	 *
	 *
	 * @return int|double
	 */
	function toDays();


	/**
	 * Return the stored time in weeks.
	 *
	 *
	 * @return int|double
	 */
	function toWeeks();

}
