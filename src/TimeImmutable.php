<?php


namespace Dakujem;


/**
 * Immutable time object.
 *
 * Once initialized, the object's value (and format) can not be changed through public methods.
 * Any modification of stored time (or format) will result in a new instance of TimeImmutable returned.
 *
 *
 * @author Andrej Rypak (dakujem) <xrypak@gmail.com>
 */
class TimeImmutable extends Time
{


	/**
	 * Set the default output and input time format.
	 * @note: produces a copy of self internally.
	 *
	 *
	 * @param string $format
	 * @return static fluent
	 */
	public function setFormat($format)
	{
		$mutation = $this->copy();
		$mutation->_setFormatMutableOverride($format);
		return $mutation;
	}


	/**
	 * Internal setter.
	 * @note: produces a copy of self internally.
	 * @internal
	 */
	protected function _set($value)
	{
		$mutation = $this->copy();
		$mutation->_setMutableOverride($value);
		return $mutation;
	}


	/**
	 * Initializes the object.
	 * @internal
	 * @note using this method breaks the immutability!
	 */
	protected function init($time, $format)
	{
		$time !== NULL && $this->_setMutableOverride($this->parse($time, $format));
		$format !== NULL && $this->_setFormatMutableOverride($format);
	}


	/**
	 * An override method used for initialization purposes only.
	 * @internal
	 * @note using this method breaks the immutability!
	 */
	protected function _setMutableOverride($value)
	{
		return parent::_set($value);
	}


	/**
	 * An override method used for initialization purposes only.
	 * @internal
	 * @note using this method breaks the immutability!
	 */
	protected function _setFormatMutableOverride($format)
	{
		return parent::setFormat($format);
	}

}
