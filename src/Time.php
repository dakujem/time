<?php


namespace Dakujem;

use Carbon\Carbon,
	DateTime,
	RuntimeException;


/**
 * Time object.
 *
 * Note: internally, the time is kept in seconds, so the minimum resolution is one second.
 *
 *
 * @author Andrej Rypak (dakujem) <xrypak@gmail.com>
 */
class Time implements TimeInterface
{
	/**
	 * @var int|NULL the time in seconds.
	 */
	protected $time = NULL;


	public function __construct($time = NULL)
	{
		if ($time !== NULL) {
			$this->_override($this->parse($time));
		}
	}


	/**
	 * Add given time value:
	 * $this + $time
	 *
	 *
	 * @param int|string|static|DateTime|Carbon $time any parsable time format
	 * @return static
	 */
	public function add($time)
	{
		return $this->_set($this->_get() + $this->parse($time));
	}


	/**
	 * Subtract given time value:
	 * $this - $time
	 *
	 *
	 * @param int|string|static|DateTime|Carbon $time any parsable time format
	 * @return static
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
	 * @return static
	 */
	public function mult($x)
	{
		return $this->_set($this->_get() * $x);
	}


	/**
	 * Divide the time by $x:
	 * $this / $x
	 *
	 * Note: division by zero results in INF.
	 *
	 * @param int|double $x
	 * @return int|double
	 */
	public function div($x)
	{
		if (!is_int($x) && !is_float($x)) {
			$x = (double) $x;
		}
		return $this->_set($x !== 0.0 && $x !== 0 ? $this->_get() / $x : INF);
	}


	/**
	 * Modulate the time by $x:
	 * $this % $x
	 *
	 * Note: division by zero results in NAN.
	 *
	 * @param int|double $x
	 * @return int|double
	 */
	public function mod($x)
	{
		if (!is_int($x) && !is_float($x)) {
			$x = (double) $x;
		}
		return $this->_set($x !== 0.0 && $x !== 0 ? (is_int($this->_get()) && is_int($x) ? $this->_get() % $x : fmod($this->_get(), $x)) : NAN);
	}


	/**
	 * Perform a less-than comparison:
	 * $this < $time
	 *
	 *
	 * @param int|string|static|DateTime|Carbon $time any parsable time format
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
	 * @param int|string|static|DateTime|Carbon $time any parsable time format
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
	 * @param int|string|static|DateTime|Carbon $time any parsable time format
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
	 * @param int|string|static|DateTime|Carbon $time any parsable time format
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
	 * @param int|string|static|DateTime|Carbon $time any parsable time format
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
	 * @param int|string|static|DateTime|Carbon $time any parsable time format
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
	 * @param int|string|static|DateTime|Carbon $time1 any parsable time format
	 * @param int|string|static|DateTime|Carbon $time2 any parsable time format
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
	 * @return static containing time between 00:00:00 and 23:59:59
	 */
	public function clipToDayTime()
	{
		$t = is_int($this->_get()) ? $this->_get() % self::DAY : fmod($this->_get(), self::DAY);
		return $this->_set($t < 0 ? $t + self::DAY : $t);
	}


	public function addSeconds($seconds = 1)
	{
		return $this->_set($this->_get() + $seconds * self::SECOND);
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
	 * @return int|double|NULL
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
		$s = $this->_get() * self::SECOND;
		return $s < 0 ? -1 : ($s === 0 ? 0 : 1);
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
	 * Return the seconds part of the time.
	 * WARNING: this does not return the time converted to seconds! For that purpose, use the toSeconds() method.
	 *
	 *  HH:MM:SS.frac
	 *        \/
	 *
	 * @return int
	 */
	public function getSeconds()
	{
		return (int) abs((int) $this->_get() % self::MINUTE);
	}


	/**
	 * Return the remaining fraction of a second. Returns NULL when the time value is integer.
	 *
	 *  HH:MM:SS.frac
	 *           \__/
	 *
	 * @return double|NULL
	 */
	public function getSecondFraction()
	{
		$val = $this->_get();
		return is_float($val) ? abs(fmod($val, self::SECOND)) : NULL;
	}


	/**
	 * Return the stored time in seconds.
	 *
	 *
	 * @return int|double
	 */
	public function toSeconds()
	{
		return $this->_get() * self::SECOND;
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
		return new DateTime((string) $this->clipToDayTime());
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
	 * Create and return a copy of self.
	 *
	 *
	 * @return static a copy of the original Time object, for fluent calls
	 */
	public function copy()
	{
		return clone $this;
	}


	/**
	 * Returns the time in seconds or NULL.
	 *
	 *
	 * @param mixed $time
	 * @return int|NULL returns NULL when the time passed is NULL or an empty string
	 * @throws RuntimeException
	 */
	protected function parse($time)
	{
		if ($time instanceof self) {
			return $time->_get();
		}
		return TimeHelper::parse($time);
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
	 * @param string|NULL $format
	 * @return string formatted time
	 */
	public function format($format = NULL)
	{
		return TimeHelper::format($this, $format);
	}


	public function __toString()
	{
		return $this->format(NULL);
	}


	/**
	 * Internal setter.
	 * @internal
	 */
	protected function _set($value)
	{
		$mutation = $this->copy();
		$mutation->_override($value);
		return $mutation;
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
	 * This method is only intended to be called from the constructor or directly after clonning.
	 *
	 *
	 * @param int|double|NULL $value
	 */
	private function _override($value)
	{
		$this->time = $value === NULL ? NULL : $value * self::SECOND;
	}


	/**
	 * Universal static factory.
	 *
	 *
	 * @param mixed $time any parsable time or NULL
	 * @return static the Time object
	 */
	public static function create($time = NULL)
	{
		return new static($time);
	}


	public static function fromSeconds($seconds)
	{
		return static::create(self::calculateSeconds(0, 0, 0, 0, $seconds));
	}


	public static function fromMinutes($minutes, $seconds = 0)
	{
		return static::create(self::calculateSeconds(0, 0, 0, $minutes, $seconds));
	}


	public static function fromHours($hours, $minutes = 0, $seconds = 0)
	{
		return static::create(self::calculateSeconds(0, 0, $hours, $minutes, $seconds));
	}


	public static function fromDays($days, $hours = 0, $minutes = 0, $seconds = 0)
	{
		return static::create(self::calculateSeconds(0, $days, $hours, $minutes, $seconds));
	}


	public static function fromWeeks($weeks, $days = 0, $hours = 0, $minutes = 0, $seconds = 0)
	{
		return static::create(self::calculateSeconds($weeks, $days, $hours, $minutes, $seconds));
	}


	private static function calculateSeconds($weeks, $days, $hours, $minutes, $seconds)
	{
		return
				$weeks * self::WEEK +
				$days * self::DAY +
				$hours * self::HOUR +
				$minutes * self::MINUTE +
				$seconds * self::SECOND
		;
	}

}
