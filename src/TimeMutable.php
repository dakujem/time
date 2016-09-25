<?php


namespace Dakujem;


/**
 * A mutable time object.
 *
 * All calls that modify the time value are accumulated instead of returning a new instance with the new value.
 * Also provides the method set().
 *
 *
 * @author Andrej Rypak (dakujem) <xrypak@gmail.com>
 */
class TimeMutable extends Time
{


	/**
	 * Set the time.
	 *
	 *
	 * @param int|string|static|DateTime|Carbon $time any parsable time format
	 * @return static
	 */
	public function set($time)
	{
		return $this->_set($this->parse($time));
	}


	/**
	 * Internal setter.
	 * @note: does NOT produce a copy of self
	 * @internal
	 */
	protected function _set($value)
	{
		$this->time = $value === NULL ? NULL : $value * self::SECOND;
		return $this;
	}

}
