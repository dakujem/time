<?php


namespace Dakujem;

use Carbon\Carbon;
use DateTime;
use RuntimeException;


/**
 * Time object.
 *
 * Note: internally, the time is kept in seconds, so the minimum resolution is one second.
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
	 * @var int|NULL the time in seconds.
	 */
	protected $time = NULL;

	const FORMAT_HMS = '?H:i:s'; //           02:34:56     or  -123:45:59
	const FORMAT_HM = '?H:i'; //              02:34        or  -123:45
	const FORMAT_HMS_SIGNED = '+H:i:s'; //   +02:34:56     or  -123:45:59
	const FORMAT_HM_SIGNED = '+H:i'; //      +02:34        or  -123:45
	const FORMAT_HMSA = 'h:i:s A'; //         12:34:56 AM  or    01:23:45 PM
	const FORMAT_HMA = 'h:i A'; //            12:34 AM     or    01:23 PM

	/**
	 * @var string format, recognized characters "?+HhisGgAa" - see the Time::format() method
	 */
	protected $format = self::FORMAT_HMS;


	public function __construct($time = NULL, $format = NULL)
	{
		$this->init($time, $format);
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
	 * Add given time value:
	 * $this + $time
	 *
	 *
	 * @param int|string|self|DateTime|Carbon $time any parsable time format
	 * @return self
	 */
	public function add($time)
	{
		return $this->_set($this->_get() + $this->parse($time));
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
		return $this->_set($this->_get() - $this->parse($time));
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
		return $this->_set($this->_get() * $x);
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
		return $this->_set($this->_get() / $x);
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
		return $this->_set($this->_get() % $x);
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
		return $this->_get() < $this->parse($time);
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
		return $this->_get() <= $this->parse($time);
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
		return $this->_get() > $this->parse($time);
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
		return $this->_get() >= $this->parse($time);
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
		return $this->_get() === $this->parse($time);
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
		return $this->_get() !== $this->parse($time);
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
				$this->_get() > $from && $this->_get() < $to :
				$this->_get() >= $from && $this->_get() <= $to;
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
		return $this->_get() >= 0 && $this->_get() < self::DAY;
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
		$t = $this->_get() % self::DAY;
		return $this->_set($t < 0 ? $t + self::DAY : $t);
	}


	public function addSeconds($seconds = 1)
	{
		return $this->_set($this->_get() + $seconds);
	}


	public function addMinutes($minutes = 1)
	{
		return $this->_set($this->_get() + $minutes * self::MINUTE);
	}


	public function addHours($hours = 1)
	{
		return $this->_set($this->_get() + $hours * self::HOUR);
	}


	public function addDays($days = 1)
	{
		return $this->_set($this->_get() + $days * self::DAY);
	}


	public function addWeeks($weeks = 1)
	{
		return $this->_set($this->_get() + $weeks * self::WEEK);
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
	 * Returns the value of the internal time member.
	 *
	 * Warning:	this method is not intended to be used for retrieving time.
	 * 			It is provided for forward compatibility and testing purposes only.
	 * 			In case you need to check for NULL value, use $time->isNull() instead.
	 *
	 *
	 * @return int|NULL
	 */
	public function getRaw()
	{
		return $this->_get();
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
		return $this->_get() === 0;
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
		return $this->_get() === NULL;
	}


	/**
	 * Indicate whether the time is negative or not.
	 *
	 *
	 * @return bool TRUE for any negative value, FALSE for positive and zero time
	 */
	public function isNegative()
	{
		return $this->_get() < 0;
	}


	/**
	 * Returns -1 when the time is negative, 0 when it is zero, +1 when it is positive.
	 *
	 *
	 * @return int
	 */
	public function getSignum()
	{
		$s = $this->_get();
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
		return (int) abs($this->_get() % self::MINUTE);
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
		return (int) abs(((int) ($this->_get() / self::MINUTE)) % self::MINUTE);
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
		return (int) abs((int) ($this->_get() / self::HOUR));
	}


	/**
	 * Return the stored time in seconds.
	 *
	 *
	 * @return int|double
	 */
	public function toSeconds()
	{
		return $this->_get() * 1;
	}


	/**
	 * Return the stored time in minutes.
	 *
	 *
	 * @return int|double
	 */
	public function toMinutes()
	{
		return $this->_get() / self::MINUTE;
	}


	/**
	 * Return the stored time in hours.
	 *
	 *
	 * @return int|double
	 */
	public function toHours()
	{
		return $this->_get() / self::HOUR;
	}


	/**
	 * Return the stored time in days.
	 *
	 *
	 * @return int|double
	 */
	public function toDays()
	{
		return $this->_get() / self::DAY;
	}


	/**
	 * Return the stored time in weeks.
	 *
	 *
	 * @return int|double
	 */
	public function toWeeks()
	{
		return $this->_get() / self::WEEK;
	}


	/**
	 * Create and return a DateTime instance using "H:i:s" format.
	 * Note: this will clip the stored time to a valid day time value using clipToDayTime() method.
	 *
	 *
	 * @return DateTime
	 */
	public function toDateTime()
	{
		return new DateTime($this->copy()->clipToDayTime()->format(self::FORMAT_HMS));
	}


	/**
	 * Fill a Carbon instance using Carbon::hour(), Carbon::minute() and Carbon::second() methods.
	 * If a Carbon instance is not provided, a new one will be created.
	 *
	 *
	 * @param Carbon $target an istance to fill
	 * @return Carbon
	 */
	public function toCarbon(Carbon $target = NULL)
	{
		if ($target === NULL) {
			$target = new Carbon();
		}
		return $target->hour($this->getHours())->minute($this->getMinutes())->second($this->getSeconds());
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
	protected function parse($time, $format = NULL)
	{
		if ($time instanceof self) {
			return $time->_get();
		} elseif ($time === NULL || $time === '') {
			return NULL;
		} elseif (is_numeric($time)) {
			// regard it as seconds
			return $time;
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
		} elseif ($time instanceof Carbon) {
			return $this->parse(array($time->hour, $time->minute, $time->second));
		} elseif ($time instanceof DateTime) {
			return $this->parse($time->format('H:i:s'));
		}
		throw new RuntimeException('Invalid argument passed.');
	}


	/**
	 * @todo sign handling should be improved
	 */
	protected function parseFormat($value, $format)
	{
		// 1/ -----------------------------------------------------------------------------------------
		// read the numbers form the input string

		$numbers = NULL;
		if (!preg_match('#([+-]?[0-9]+)(.([+-]?[0-9]+)?(.([+-]?[0-9]+)?)?)?#', $value, $numbers)) { // PREG_OFFSET_CAPTURE
			return NULL;
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
		$hpos = $hpos1 !== FALSE ? $hpos1 : stripos($format, 'g');
		$ipos = stripos($format, 'i');
		$spos = stripos($format, 's');
		// hpos, ipos, spos contain the position in $format
		if ($hpos === FALSE && $ipos === FALSE && $spos === FALSE) {
			return NULL;
		}
		// $keys contain valid references to hours, minutes and seconds
		$h = $m = $s = 0;
		$keys = [];
		if ($hpos !== FALSE) {
			$keys[$hpos] = &$h; // reference to hours
		}
		if ($ipos !== FALSE) {
			$keys[$ipos] = &$m; // reference to minutes
		}
		if ($spos !== FALSE) {
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

		$hneg = substr($h, 0, 1) === '-'; // hours negative
		$mneg = substr($m, 0, 1) === '-'; // minutes negative
		$sneg = substr($s, 0, 1) === '-'; // seconds negative
		if (TRUE) {
			$h = (int) $h;
			$m = (int) $m;
			$s = (int) $s;
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
			//TODO this does not cover the case when format "?i:s" is used
		}

		// 4/ -----------------------------------------------------------------------------------------
		// check for and correct the 12-hour format (if used)

		$f12h = stripos($format, 'a') !== FALSE; // check for 12-h format?
		if ($f12h) {
			if ($h > 12 || $h < 0) { // invalid 12h format
				return NULL;
			}
			$a = stripos($value, 'am');
			if ($a === FALSE) {
				$p = stripos($value, 'pm');
				if ($p !== FALSE) {
					$am = FALSE;
				} else {
					return NULL; // AM or PM not found
				}
			} else {
				$am = TRUE;
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

		return
				$h * self::HOUR +
				$m * self::MINUTE +
				$s;
	}


	/**
	 * Initializes the object.
	 * @internal
	 */
	protected function init($time, $format)
	{
		$time !== NULL && $this->set($time, $format);
		$format !== NULL && $this->setFormat($format);
	}


	/**
	 * Internal setter.
	 * @internal
	 */
	protected function _set($value)
	{
		$this->time = $value === NULL ? NULL : (int) $value; // TODO use * 1 instead of (int)
		return $this;
	}


	/**
	 * Internal getter.
	 * @internal
	 */
	protected function _get()
	{
		return $this->time;
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
	 * Universal static factory.
	 *
	 *
	 * @param mixed $time
	 * @param string|NULL $format optional, use when passing a string as $time
	 * @return static the Time object
	 */
	public static function create($time = NULL, $format = NULL)
	{
		return new static($time, $format);
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
