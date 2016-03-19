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


	public function __construct($format = NULL)
	{
		$format !== NULL && $this->setFormat($format);
	}


	public function create($time)
	{
		return $this->createEmpty()->set($time)->setFormat($this->getFormat());
	}


	public function createEmpty()
	{
		return new Time();
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

}
