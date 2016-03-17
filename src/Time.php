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

	private $time = NULL;

	/**
	 * @var string recognized characters "?+HhisGgAa" - see php date() function for "HhisGgAa", '?' is used for the minus sign, '+' is used for minus and plus sign
	 */
	private $format = '?H:i:s';


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


	public function setFormatHoursMinutes()
	{
		return $this->setFormat('?H:i');
	}


	public function setFormatHoursMinutesSeconds()
	{
		return $this->setFormat('?H:i:s');
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


	// add, sub, porovnania, between
	// toMinutes, toSeconds, toHours[, toDays, toWeeks]
	// toCarbon, toDateTime
	// clipToDay - oreze na 24 hodin, t.j. ak je cas vacsi ako 24 hod, zahodi zvysok (vytvori kopiu seba)

	public function format($format)
	{
		$neg = $this->isNegative();
		$h = $this->getHours();
		$m = $this->getMinutes();
		$s = $this->getSeconds();
		$v = $this->isValidDayTime();
		return str_replace(['?', '+', 'H', 'h', 'G', 'g', 'i', 's', 'A', 'a'], [
			$neg ? '-' : '', // ?
			$neg ? '-' : '+', // +
			sprintf('%02d', $h), // H
			sprintf('%02d', $v ? $h % 12 : $h ), // h
			$h, // G
			$v ? $h % 12 : $h, // g
			sprintf('%02d', $m), // i
			sprintf('%02d', $s), // s
			$v ? ($h < 12 ? 'AM' : 'PM') : '', // A
			$v ? ($h < 12 ? 'am' : 'pm') : '', // a
				], $format);
	}


	public function __toString()
	{
		return $this->format($this->getFormat());
	}

}
