<?php


namespace Dakujem;


/**
 * TimeFactory - a factory service.
 *
 * It can be used to create Time or TimeMutable objects with parsing input beforehand.
 * Its advantage for users is the ability to specify the input format (for parsing strings).
 * The factory can also be used when static factory calls of the time objects in code are not desired.
 *
 * TimeFactory::$immutable flag determins, which of the two time objects will be created - Time (immutable) or TimeMutable.
 * TimeFactory::$format will be passed to the TimeHelper::parse($rawTime, $format) call.
 *
 * 
 * @author Andrej Rypak <xrypak@gmail.com>
 */
final class TimeFactory
{
	private $format = TimeHelper::FORMAT_HMS;
	private $immutable = TRUE;


	public function __construct($format = NULL, $immutable = NULL)
	{
		$format !== NULL && $this->setFormat($format);
		$immutable !== NULL && $this->setImmutable($immutable);
	}


	public function create($rawTime = NULL, $format = NULL)
	{
		if ($format === NULL) {
			$format = $this->getFormat();
		}
		$seconds = TimeHelper::parse($rawTime, $format);
		return $this->getImmutable() ? new Time($seconds) : new TimeMutable($seconds);
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
