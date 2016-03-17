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
	private $format = '?H:i:s';


	public function __construct($format = NULL)
	{
		$format !== NULL && $this->setFormat($format);
	}


	public function create($time)
	{
		return new Time($time);
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

}
