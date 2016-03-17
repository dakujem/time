<?php


namespace Dakujem;

use RuntimeException;


/**
 * Time.
 *
 * 
 * @author Andrej Rypak <xrypak@gmail.com>
 */
class Time
{
	const MIN = 60;
	const HOUR = 3600; //   60 * 60
	const DAY = 86400; //   60 * 60 * 24
	const WEEK = 604800; // 60 * 60 * 24 * 7

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


	public function __construct($time = NULL)
	{
		$time !== NULL && $this->parseTime($time);
	}


	public function add(self $time)
	{
		//TODO param has to be a Time instance ??
		return $this->parseTime($this->toSeconds() + $time->toSeconds());
	}


	public function sub(self $time)
	{
		//TODO param has to be a Time instance ??
		return $this->parseTime($this->toSeconds() - $time->toSeconds());
	}


	/**
	 * Multiply the time by $x.
	 *
	 *
	 * @param int|double $x
	 * @return self
	 */
	public function mult($x)
	{
		return $this->parseTime($this->toSeconds() * $x);
	}


	/**
	 * Divide the time by $x.
	 *
	 *
	 * @param int|double $x
	 * @return self
	 */
	public function div($x)
	{
		return $this->parseTime($this->toSeconds() / $x);
	}


	/**
	 * Modulate the time by $x.
	 *
	 *
	 * @param int $x
	 * @return self
	 */
	public function mod($x)
	{
		return $this->parseTime($this->toSeconds() % $x);
	}


	public function lt(self $time)
	{
		//TODO param has to be a Time instance ??
		return $this->toSeconds() < $time->toSeconds();
	}


	public function lte(self $time)
	{
		//TODO param has to be a Time instance ??
		return $this->toSeconds() <= $time->toSeconds();
	}


	public function gt(self $time)
	{
		//TODO param has to be a Time instance ??
		return $this->toSeconds() > $time->toSeconds();
	}


	public function gte(self $time)
	{
		//TODO param has to be a Time instance ??
		return $this->toSeconds() >= $time->toSeconds();
	}


	public function eq(self $time)
	{
		//TODO param has to be a Time instance ??
		return $this->toSeconds() === $time->toSeconds();
	}


	public function neq(self $time)
	{
		//TODO param has to be a Time instance ??
		return $this->toSeconds() !== $time->toSeconds();
	}


	public function between(self $time1, self $time2, $sharp = FALSE)
	{
		//TODO params have to be Time instances ??
		if ($time1->lte($time2)) {
			$from = $time1;
			$to = $time2;
		} else {
			$from = $time2;
			$to = $time1;
		}
		return
				$sharp ?
				$this->toSeconds() > $from->toSeconds() && $this->toSeconds() < $to->toSeconds() :
				$this->toSeconds() >= $from->toSeconds() && $this->toSeconds() <= $to->toSeconds();
	}


	public function isValidDayTime()
	{
		return $this->toSeconds() >= 0 && $this->toSeconds() < self::DAY;
	}


	public function clipToDayTime()
	{
		//TODO should this create a new instance?
		$t = $this->toSeconds() % self::DAY;
		return static::fromSeconds($t < 0 ? $t + self::DAY : $t);
	}


	public function getFormat()
	{
		return $this->format;
	}


	public function setFormat($format)
	{
		$this->format = $format;
		return $this;
	}


	public function useFormatHoursMinutes()
	{
		return $this->setFormat(self::FORMAT_HM);
	}


	public function useFormatHoursMinutesSeconds()
	{
		return $this->setFormat(self::FORMAT_HMS);
	}


	public function setTime($time)
	{
		//TODO
		return $this->setSeconds($time);
	}


//	public function setSeconds($seconds)
//	{
//		$this->time = (int) $seconds;
//		return $this;
//	}


	public function isNegative()
	{
		return $this->toSeconds() < 0;
	}


	public function getSeconds()
	{
		return abs($this->time % self::MIN);
	}


	public function getMinutes()
	{
		return abs(((int) ($this->time / self::MIN)) % self::MIN);
	}


	public function getHours()
	{
		return abs((int) ($this->time / self::HOUR));
	}


	public function toSeconds()
	{
		return $this->time;
	}


	public function toMinutes()
	{
		return $this->time / self::MIN;
	}


	public function toHours()
	{
		return $this->time / self::HOUR;
	}


	public function toDays()
	{
		return $this->time / self::DAY;
	}


	public function toWeeks()
	{
		return $this->time / self::WEEK;
	}

	//TODO toCarbon, toDateTime


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


	public function copy()
	{
		return clone $this;
	}


	public function parseTime($time, $format = NULL)
	{
		if ($time === NULL || $time === '') {
			// reset to NULL
			$this->set(NULL);
		} elseif ($time instanceof self) {
			$this->set($time->toSeconds());
		} elseif (is_numeric($time)) {
			// regard it as seconds
			$this->seconds = (int) $time;
		} elseif (is_string($time)) {
			// regard it as time string
			$this->setFromFormat($time, $format === NULL ? self::FORMAT_HMS : $format);
		} elseif (is_array($time)) {
			// [H, m, s]
			//TODO what if there are negative values?
			$h = reset($time) * self::HOUR;
			$m = next($time) * self::MIN;
			$s = next($time);
			$this->set($h + $m + $s);
		} else {
			//TODO carbon / datetime
			throw new RuntimeException('Invalid argument passed.');
		}
		return $this;
	}


	private function set($value)
	{
		$this->seconds = $value;
		return $this;
	}


	private function setFromFormat($value, $format)
	{
		//TODO implement
		throw new RuntimeException('Not implemented yet.');
		return $this;
	}


	public static function fromSeconds($seconds)
	{
		$instance = new static();
		$instance->parseTime((int) $seconds);
		return $instance;
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
		return $instance->parseTime($time, $format);
	}

}
