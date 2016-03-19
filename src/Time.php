<?php


namespace Dakujem;

use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use RuntimeException;


/**
 * Time.
 *
 *
 * Note: internally, the time is kept in seconds, so the minimum resolution is one second.
 *
 *
 * @todo: toCarbon, toDateTime
 *
 *
 * @todo should the Time object be immutable? should i provide an immutable alternative?
 *
 *
 * @author Andrej Rypak (dakujem) <xrypak@gmail.com>
 */
class Time
{
	const MINUTE = 60;
	const HOUR = 3600; //   60 * 60
	const DAY = 86400; //   60 * 60 * 24
	const WEEK = 604800; // 60 * 60 * 24 * 7
	const HOUR_MINUTES = 60;
	const DAY_MINUTES = 1440; //   60 * 24
	const WEEK_MINUTES = 10080; // 60 * 60 * 24
	const DAY_HOURS = 24;
	const WEEK_HOURS = 168; // 24 * 7
	const WEEK_DAYS = 7;

	/**
	 * @var int the time in seconds.
	 */
	private $time = NULL;

	const FORMAT_HMS = '?H:i:s'; //           02:34:56     or  -123:45:59
	const FORMAT_HM = '?H:i'; //              02:34        or  -123:45
	const FORMAT_HMS_SIGNED = '+H:i:s'; //   +02:34:56     or  -123:45:59
	const FORMAT_HM_SIGNED = '+H:i'; //      +02:34        or  -123:45
	const FORMAT_HMSA = 'h:i:s A'; //         12:34:56 AM  or    01:23:45 PM
	const FORMAT_HMA = 'h:i A'; //            12:34 AM     or    01:23 PM

	/**
	 * @var string format, recognized characters "?+HhisGgAa" - see the Time::format() method
	 */
	private $format = self::FORMAT_HMS;


	public function __construct($time = NULL, $format = NULL)
	{
		$time !== NULL && $this->set($time, $format);
		$format !== NULL && $this->setFormat($format);
	}


	/**
	 * Add given time value:
	 * $this + $time
	 *
	 *
	 * @param int|string|self|DateTime|Carbon $time any parsable time format
	 * @return self
	 */
	public function add($time)
	{
		return $this->_set($this->toSeconds() + $this->parse($time));
	}


	/**
	 * Substract given time value:
	 * $this - $time
	 *
	 *
	 * @param int|string|self|DateTime|Carbon $time any parsable time format
	 * @return self
	 */
	public function sub($time)
	{
		return $this->_set($this->toSeconds() - $this->parse($time));
	}


	/**
	 * Multiply the time by $x:
	 * $this * $x
	 *
	 *
	 * @param int|double $x
	 * @return self
	 */
	public function mult($x)
	{
		return $this->_set($this->toSeconds() * $x);
	}


	/**
	 * Divide the time by $x:
	 * $this / $x
	 *
	 *
	 * @param int|double $x
	 * @return self
	 */
	public function div($x)
	{
		return $this->_set($this->toSeconds() / $x);
	}


	/**
	 * Modulate the time by $x:
	 * $this % $x
	 *
	 *
	 * @param int $x
	 * @return self
	 */
	public function mod($x)
	{
		return $this->_set($this->toSeconds() % $x);
	}


	/**
	 * Perform a less-than comparison:
	 * $this < $time
	 *
	 *
	 * @param int|string|self|DateTime|Carbon $time any parsable time format
	 * @return bool
	 */
	public function lt($time)
	{
		return $this->toSeconds() < $this->parse($time);
	}


	/**
	 * Perform a less-than-or-equal comparison:
	 * $this <= $time
	 *
	 *
	 * @param int|string|self|DateTime|Carbon $time any parsable time format
	 * @return bool
	 */
	public function lte($time)
	{
		return $this->toSeconds() <= $this->parse($time);
	}


	/**
	 * Perform a greater-than comparison:
	 * $this > $time
	 *
	 *
	 * @param int|string|self|DateTime|Carbon $time any parsable time format
	 * @return bool
	 */
	public function gt($time)
	{
		return $this->toSeconds() > $this->parse($time);
	}


	/**
	 * Perform a greater-than-or-equal comparison:
	 * $this >= $time
	 *
	 *
	 * @param int|string|self|DateTime|Carbon $time any parsable time format
	 * @return bool
	 */
	public function gte($time)
	{
		return $this->toSeconds() >= $this->parse($time);
	}


	/**
	 * Perform a equal-to comparison:
	 * $this == $time
	 *
	 *
	 * @param int|string|self|DateTime|Carbon $time any parsable time format
	 * @return bool
	 */
	public function eq($time)
	{
		return $this->toSeconds() === $this->parse($time);
	}


	/**
	 * Perform a not-equal-to comparison:
	 * $this != $time
	 *
	 *
	 * @param int|string|self|DateTime|Carbon $time any parsable time format
	 * @return bool
	 */
	public function neq($time)
	{
		return $this->toSeconds() !== $this->parse($time);
	}


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
	 * @param int|string|self|DateTime|Carbon $time1 any parsable time format
	 * @param int|string|self|DateTime|Carbon $time2 any parsable time format
	 * @param bool $sharp [=FALSE] exclude the extremes of the interval?
	 * @return bool
	 */
	public function between($time1, $time2, $sharp = FALSE)
	{
		$t1 = $this->parse($time1);
		$t2 = $this->parse($time2);
		if ($t1 <= $t2) {
			$from = $t1;
			$to = $t2;
		} else {
			$from = $t2;
			$to = $t1;
		}
		return
				$sharp ?
				$this->toSeconds() > $from && $this->toSeconds() < $to :
				$this->toSeconds() >= $from && $this->toSeconds() <= $to;
	}


	/**
	 * Is the time a valid day time?
	 * e.g. Is the time between 00:00:00 and 23:59:59 ?
	 *
	 *
	 * @return bool
	 */
	public function isValidDayTime()
	{
		return $this->toSeconds() >= 0 && $this->toSeconds() < self::DAY;
	}


	/**
	 * Clip the time to a valid day time between 00:00:00 and 23:59:59.
	 * This will perform a modulo-DAY operation: $this % DAY
	 *
	 *
	 * @return self containing time between 00:00:00 and 23:59:59
	 */
	public function clipToDayTime()
	{
		$t = $this->toSeconds() % self::DAY;
		return $this->_set($t < 0 ? $t + self::DAY : $t);
	}


	/**
	 * Set the time.
	 * The input is parsed.
	 *
	 *
	 * @param int|string|self|DateTime|Carbon $time
	 * @param string|NULL $format
	 * @return self fluent
	 */
	public function set($time, $format = NULL)
	{
		return $this->_set($this->parse($time, $format));
	}


	/**
	 * Internal setter.
	 *
	 *
	 * @internal
	 * @param int|NULL $value
	 * @return self fluent
	 */
	private function _set($value)
	{
		$this->time = $value === NULL ? NULL : (int) $value;
		return $this;
	}


	public function addSeconds($seconds = 1)
	{
		return $this->_set($this->toSeconds() + $seconds);
	}


	public function addMinutes($minutes = 1)
	{
		return $this->_set($this->toSeconds() + $minutes * self::MINUTE);
	}


	public function addHours($hours = 1)
	{
		return $this->_set($this->toSeconds() + $hours * self::HOUR);
	}


	public function addDays($days = 1)
	{
		return $this->_set($this->toSeconds() + $days * self::DAY);
	}


	public function addWeeks($weeks = 1)
	{
		return $this->_set($this->toSeconds() + $weeks * self::WEEK);
	}


	public function subSeconds($seconds = 1)
	{
		return $this->addSeconds($seconds * -1);
	}


	public function subMinutes($minutes = 1)
	{
		return $this->addMinutes($minutes * -1);
	}


	public function subHours($hours = 1)
	{
		return $this->addHours($hours * -1);
	}


	public function subDays($days = 1)
	{
		return $this->addDays($days * -1);
	}


	public function subWeeks($weeks = 1)
	{
		return $this->addWeeks($weeks * -1);
	}


	/**
	 * Indicate whether the time is equal to zero.
	 * @see isNULL()
	 *
	 *
	 * @return bool
	 */
	public function isZero()
	{
		return $this->toSeconds() === 0;
	}


	/**
	 * Indicate whether the time is NULL,
	 * which means the time was not set or has been reset to NULL.
	 * @see isZero()
	 *
	 *
	 * @return bool
	 */
	public function isNULL()
	{
		return $this->time === NULL;
	}


	/**
	 * Indicate whether the time is negative or not.
	 *
	 *
	 * @return bool TRUE for any negative value, FALSE for positive and zero time
	 */
	public function isNegative()
	{
		return $this->toSeconds() < 0;
	}


	/**
	 * Returns -1 when the time is negative, 0 when it is zero, +1 when it is positive.
	 *
	 *
	 * @return int
	 */
	public function getSignum()
	{
		$s = $this->toSeconds();
		return $s < 0 ? -1 : ($s === 0 ? 0 : 1);
	}


	/**
	 * Return the seconds part of the time.
	 * WARNING: this does not return the time converted to seconds! For that purpose, use the toSeconds() method.
	 *
	 *  HH:MM:SS
	 *        \/
	 *
	 * @return int
	 */
	public function getSeconds()
	{
		return (int) abs($this->toSeconds() % self::MINUTE);
	}


	/**
	 * Return the minute part of the time.
	 * WARNING: this does not return the time converted to minutes! For that purpose, use the toMinutes() method.
	 *
	 *  HH:MM:SS
	 *     \/
	 *
	 * @return int
	 */
	public function getMinutes()
	{
		return (int) abs(((int) ($this->toSeconds() / self::MINUTE)) % self::MINUTE);
	}


	/**
	 * Return the hour part of the time.
	 * WARNING: this does not return the time converted to hours! For that purpose, use the toHours() method.
	 *
	 *  HH:MM:SS
	 *  \/
	 *
	 * @return int
	 */
	public function getHours()
	{
		return (int) abs((int) ($this->toSeconds() / self::HOUR));
	}


	/**
	 * Return the time in seconds.
	 *
	 *
	 * @return int
	 */
	public function toSeconds()
	{
		return $this->time;
	}


	/**
	 * Return the time in minutes.
	 *
	 *
	 * @return int|double
	 */
	public function toMinutes()
	{
		return $this->toSeconds() / self::MINUTE;
	}


	/**
	 * Return the time in hours.
	 *
	 *
	 * @return int|double
	 */
	public function toHours()
	{
		return $this->toSeconds() / self::HOUR;
	}


	/**
	 * Return the time in days.
	 *
	 *
	 * @return int|double
	 */
	public function toDays()
	{
		return $this->toSeconds() / self::DAY;
	}


	/**
	 * Return the time in weeks.
	 *
	 *
	 * @return int|double
	 */
	public function toWeeks()
	{
		return $this->toSeconds() / self::WEEK;
	}


	/**
	 * Get the default output and input time format.
	 *
	 *
	 * @return string
	 */
	public function getFormat()
	{
		return $this->format;
	}


	/**
	 * Set the default output and input time format.
	 *
	 *
	 * @param string $format
	 * @return self fluent
	 */
	public function setFormat($format)
	{
		$this->format = $format;
		return $this;
	}


	/**
	 * Set the default output and input time format to hours:minutes (HH:MM)
	 *
	 *
	 * @return self fluent
	 */
	public function useFormatHoursMinutes()
	{
		return $this->setFormat(self::FORMAT_HM);
	}


	/**
	 * Set the default output and input time format to hours:minutes:seconds (HH:MM:SS).
	 *
	 *
	 * @return self fluent
	 */
	public function useFormatHoursMinutesSeconds()
	{
		return $this->setFormat(self::FORMAT_HMS);
	}


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
	 * @param string $format
	 * @return string formatted time
	 */
	public function format($format)
	{
		$neg = $this->isNegative();
		$v = $this->isValidDayTime();
		$h = $this->getHours();
		$h12 = $v ? ($h % 12 === 0 ? 12 : $h % 12) : $h;
		$m = $this->getMinutes();
		$s = $this->getSeconds();
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
				], $format);
	}


	/**
	 * @todo custom callback formatter ??
	 */
	public function __toString()
	{
		return $this->format($this->getFormat());
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
	private function parse($time, $format = NULL)
	{
		if ($time instanceof self) {
			return $time->toSeconds();
		} elseif ($time === NULL || $time === '') {
			return NULL;
		} elseif (is_numeric($time)) {
			// regard it as seconds
			return (int) $time;
		} elseif (is_string($time)) {
			// regard it as time string
			return $this->parseFormat($time, $format === NULL || $format === '' ? $this->getFormat() : $format);
		} elseif (is_array($time)) {
			// [H, m, s]
			//TODO what if there are negative values?
			$h = reset($time) * self::HOUR;
			$m = next($time) * self::MINUTE;
			$s = next($time);
			return $h + $m + $s;
		} elseif (/* $time instanceof Carbon || */ $time instanceof DateTime) { // note: carbon descends from DateTime
			return $this->parse($time->format('H:i:s'));
		}
		throw new RuntimeException('Invalid argument passed.');
	}


	private function parseFormat($value, $format)
	{
		//TODO reimplement using custom format reading...
		$tz = new DateTimeZone('UTC');
		return (new DateTime($value, $tz))->getTimestamp() - (new DateTime('00:00:00', $tz))->getTimestamp();
	}


	/**
	 * Create and return a copy of self.
	 *
	 *
	 * @return self a copy of the original Time object, for fluent calls
	 */
	public function copy()
	{
		return clone $this;
	}


	/**
	 * Universal factory.
	 *
	 *
	 * @param mixed $time
	 * @param string|NULL $format optional, use when passing a string as $time
	 * @return static the Time object
	 */
	public static function create($time = NULL, $format = NULL)
	{
		$instance = new static();
		return $instance->set($time, $format);
	}


	public static function fromSeconds($seconds)
	{
		return static::create((int) $seconds);
	}


	public static function fromMinutes($minutes, $seconds = 0)
	{
		return static::create((int) ($minutes * self::MINUTE + $seconds));
	}


	public static function fromHours($hours, $minutes = 0, $seconds = 0)
	{
		return static::create((int) ( $hours * self::HOUR + $minutes * self::MINUTE + $seconds));
	}


	public static function fromDays($days, $hours = 0, $minutes = 0, $seconds = 0)
	{
		return static::create((int) ($days * self::DAY + $hours * self::HOUR + $minutes * self::MINUTE + $seconds));
	}


	public static function fromWeeks($weeks, $days = 0, $hours = 0, $minutes = 0, $seconds = 0)
	{
		return static::create((int) ($weeks * self::WEEK + $days * self::DAY + $hours * self::HOUR + $minutes * self::MINUTE + $seconds));
	}

}
