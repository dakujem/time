<?php


namespace Dakujem;


/**
 * TimeFactory.
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
