<?php


namespace Dakujem;


/**
 * TimeFactory - a factory service.
 * Use it to create Time or TimeImmutable objects with a certain format setting
 * and/or when you do not want to call static factories of the time objects in your code.
 *
 * TimeFactory::$immutable flag determins, which of the two time objects will be created - Time or TimeImmutable.
 * TimeFactory::$format will be set to all created classes.
 *
 * 
 * @author Andrej Rypak <xrypak@gmail.com>
 */
class TimeFactory
{
	private $format = Time::FORMAT_HMS;
	private $immutable = FALSE;


	public function __construct($format = NULL, $immutable = NULL)
	{
		$format !== NULL && $this->setFormat($format);
		$immutable !== NULL && $this->setImmutable($immutable);
	}


	public function create($time = NULL, $format = NULL)
	{
		return $this->getImmutable() ? new TimeImmutable($time, $format) : new Time($time, $format);
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
		return $this->setFormat(Time::FORMAT_HM);
	}


	public function useFormatHoursMinutesSeconds()
	{
		return $this->setFormat(Time::FORMAT_HMS);
	}


	public function getImmutable()
	{
		return $this->immutable;
	}


	public function setImmutable($immutable)
	{
		$this->immutable = !!$immutable;
		return $this;
	}

}
