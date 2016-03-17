<?php


namespace Dakujem;


/**
 * Time.
 *
 * 
 * @author Andrej Rypak <xrypak@gmail.com>
 */
class Time
{
	const MIN = 60;
	const HOUR = 3600; // 60 * 60
	const DAY = 86400; // 60 * 60 * 24
	const WEEK = 604800; // 60 * 60 * 24 * 7

	/**
	 * @var int the time in seconds.
	 */
	private $time = NULL;

	const FORMAT_HMS = '?H:i:s';
	const FORMAT_HM = '?H:i';
	const FORMAT_HMS_SIGNED = '+H:i:s';
	const FORMAT_HM_SIGNED = '+H:i';
	const FORMAT_HMSA = 'h:i:s A';
	const FORMAT_HMA = 'h:i A';

	/**
	 * @var string format, recognized characters "?+HhisGgAa" - see the Time::format() method
	 */
	private $format = self::FORMAT_HMS;


	public function __construct($time = NULL)
	{
		$time !== NULL && $this->setTime($time);
	}


	public function add(self $time)
	{
		return $this->setTime($this->getTime() + $time->getTime());
	}


	public function sub(self $time)
	{
		return $this->setTime($this->getTime() - $time->getTime());
	}


	public function mult($x)
	{
		return $this->setTime($this->getTime() * $x);
	}


	public function div($x)
	{
		return $this->setTime($this->getTime() / $x);
	}


	public function mod($x)
	{
		return $this->setTime($this->getTime() % $x);
	}


	public function lt(self $time)
	{
		return $this->getTime() < $time->getTime();
	}


	public function lte(self $time)
	{
		return $this->getTime() <= $time->getTime();
	}


	public function gt(self $time)
	{
		return $this->getTime() > $time->getTime();
	}


	public function gte(self $time)
	{
		return $this->getTime() >= $time->getTime();
	}


	public function eq(self $time)
	{
		return $this->getTime() === $time->getTime();
	}


	public function neq(self $time)
	{
		return $this->getTime() !== $time->getTime();
	}


	public function between(self $time1, self $time2, $sharp = FALSE)
	{
		if ($time1->lte($time2)) {
			$from = $time1;
			$to = $time2;
		} else {
			$from = $time2;
			$to = $time1;
		}
		return
				$sharp ?
				$this->getTime() > $from->getTime() && $this->getTime() < $to->getTime() :
				$this->getTime() >= $from->getTime() && $this->getTime() <= $to->getTime();
	}


	public static function fromSeconds($seconds)
	{
		$instance = new static();
		$instance->setSeconds($seconds);
		return $instance;
	}


	public function isValidDayTime()
	{
		return $this->getTime() >= 0 && $this->getTime() < self::DAY;
	}


	public function clipToDayTime()
	{
		//TODO should this create a new instance?
		$t = $this->getTime() % self::DAY;
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


	public function getTime()
	{
		return $this->toSeconds();
	}


	public function setTime($time)
	{
		//TODO
		return $this->setSeconds($time);
	}


	public function setSeconds($seconds)
	{
		$this->time = (int) $seconds;
		return $this;
	}


	public function isNegative()
	{
		return $this->getTime() < 0;
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

}
