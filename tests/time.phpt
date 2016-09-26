<?php

/**
 * This file is a part of dakujem/time package.
 * @author Andrej Rypak (dakujem) <xrypak@gmail.com>
 */


namespace Dakujem\Test\Time;

require_once __DIR__ . '/bootstrap.php';

use Carbon\Carbon,
	Dakujem\Time,
	Dakujem\TimeMutable,
	DateTime,
	Tester\Assert,
	Tester\TestCase;


/**
 * Unit test for Time and TimeImmutable classes.
 *
 *
 * The test methods (starting with "test") will all be run in the order of definition.
 * Before each method call, setUp() is called, and tearDown() after the method run.
 * @see Nette Tester ("nette/tester") for more information
 *
 *
 * @author Andrej Rypak (dakujem) <xrypak@gmail.com>
 */
class TimeTest extends TestCase
{


	protected function setUp()
	{
		parent::setUp();
	}


	protected function tearDown()
	{
		parent::tearDown();
	}


	//--------------------------------------------------------------------------
	//----------------------- Test methods -------------------------------------


	public function testConstants()
	{
		// time constants
		Assert::same(1, Time::SECOND);
		Assert::same(60, Time::MINUTE);
		Assert::same(60, Time::HOUR_MINUTES);
		Assert::same(24, Time::DAY_HOURS);
		Assert::same(7, Time::WEEK_DAYS);

		// calculated time constants (seconds)
		Assert::same(Time::HOUR_MINUTES * Time::MINUTE, Time::HOUR);
		Assert::same(Time::DAY_HOURS * Time::HOUR, Time::DAY);
		Assert::same(Time::WEEK_DAYS * Time::DAY, Time::WEEK);

		// calculated time constants (minutes)
		Assert::same(Time::DAY_HOURS * Time::MINUTE, Time::DAY_MINUTES);
		Assert::same(Time::WEEK_DAYS * Time::DAY_MINUTES, Time::WEEK_MINUTES);

		// calculated time constants (hours)
		Assert::same(Time::WEEK_DAYS * Time::DAY_HOURS, Time::WEEK_HOURS);
	}


	public function testInternals()
	{
		// test the raw getter and basic constructor
		// Note: the raw getter is not intended for time getting outside the test environment
		Assert::same(NULL, (new Time)->getRaw());
		Assert::same(1, (new Time(1))->getRaw());
		Assert::same(-1, (new Time(-1))->getRaw());

		// min / max for int - these values are approximate
		Assert::same(3550 * Time::WEEK, (new Time(3550 * Time::WEEK))->getRaw());
		Assert::same(-3550 * Time::WEEK, (new Time(-3550 * Time::WEEK))->getRaw());

		// test the internal workings of toSeconds - needed for further tests
		Assert::same((new Time)->toSeconds(), (new Time)->getRaw() * Time::SECOND);
		Assert::same((new Time(1))->toSeconds(), (new Time(1))->getRaw() * Time::SECOND);
		Assert::same((new Time(100))->toSeconds(), (new Time(100))->getRaw() * Time::SECOND);
		Assert::same((new Time(-1))->toSeconds(), (new Time(-1))->getRaw() * Time::SECOND);
		Assert::same((new Time(-100))->toSeconds(), (new Time(-100))->getRaw() * Time::SECOND);
		Assert::same((new Time(1.4))->toSeconds(), (new Time(1.4))->getRaw() * Time::SECOND);
		Assert::same((new Time(-1.4))->toSeconds(), (new Time(-1.4))->getRaw() * Time::SECOND);

		// test the basic static factory - fromSeconds - needed for further test
		Assert::same(0, Time::fromSeconds(NULL)->getRaw());
		Assert::same(0, Time::fromSeconds('')->getRaw());
		Assert::same(0, Time::fromSeconds('foo')->getRaw());
		Assert::same(0, Time::fromSeconds('0')->getRaw());
		Assert::same(4, Time::fromSeconds('4')->getRaw());
		Assert::same(0, Time::fromSeconds(FALSE)->getRaw());
		Assert::same(1, Time::fromSeconds(TRUE)->getRaw());
		Assert::same(0, Time::fromSeconds(0)->getRaw());
		Assert::same(1, Time::fromSeconds(1)->getRaw());
		Assert::same(-12, Time::fromSeconds(-12)->getRaw());

		// test the copy method
		$t = new Time;
		Assert::equal($t, $t->copy());
		Assert::notSame($t, $t->copy());
		$t1 = new Time(1);
		Assert::equal($t1, $t1->copy());
		Assert::notSame($t1, $t1->copy());
		$t2 = new Time(-1);
		Assert::equal($t2, $t2->copy());
		Assert::notSame($t2, $t2->copy());
		$t3 = new Time(1, 'foo');
		Assert::equal($t3, $t3->copy());
		Assert::notSame($t3, $t3->copy());
		$t4 = new Time(NULL, 'foo');
		Assert::equal($t4, $t4->copy());
		Assert::notSame($t4, $t4->copy());
	}


	public function testGetters()
	{
		$uninitialized = new Time;
		Assert::same(TRUE, $uninitialized->isNull());
		Assert::same(FALSE, $uninitialized->isZero());
		Assert::same(0, $uninitialized->getSeconds());
		Assert::same(0, $uninitialized->getMinutes());
		Assert::same(0, $uninitialized->getHours());
		Assert::same(NULL, $uninitialized->getSecondFraction());
		Assert::same(FALSE, $uninitialized->isNegative());
		Assert::same(0, $uninitialized->getSignum());

		Assert::same(0, $uninitialized->toSeconds());
		Assert::same(0, $uninitialized->toMinutes());
		Assert::same(0, $uninitialized->toHours());
		Assert::same(0, $uninitialized->toDays());
		Assert::same(0, $uninitialized->toWeeks());


		$timeZero = Time::fromSeconds(0);
		Assert::same(FALSE, $timeZero->isNULL());
		Assert::same(TRUE, $timeZero->isZero());
		Assert::same(0, $timeZero->getSeconds());
		Assert::same(0, $timeZero->getMinutes());
		Assert::same(0, $timeZero->getHours());
		Assert::same(NULL, $timeZero->getSecondFraction());
		Assert::same(FALSE, $timeZero->isNegative());
		Assert::same(0, $timeZero->getSignum());

		Assert::same(0, $timeZero->toSeconds());
		Assert::same(0, $timeZero->toMinutes());
		Assert::same(0, $timeZero->toHours());
		Assert::same(0, $timeZero->toDays());
		Assert::same(0, $timeZero->toWeeks());


		$seconds = 6873 * Time::HOUR + 54 * Time::MINUTE + 18; // 6873 hours 54 minutes 18 seconds

		$time = Time::fromSeconds($seconds);
		Assert::same(FALSE, $time->isNULL());
		Assert::same(FALSE, $time->isZero());
		Assert::same(18, $time->getSeconds());
		Assert::same(54, $time->getMinutes());
		Assert::same(6873, $time->getHours());
		Assert::same(NULL, $time->getSecondFraction());
		Assert::same(FALSE, $time->isNegative());
		Assert::same(1, $time->getSignum());

		Assert::same($seconds, $time->toSeconds());
		Assert::same($seconds / Time::MINUTE, $time->toMinutes());
		Assert::same($seconds / Time::HOUR, $time->toHours());
		Assert::same($seconds / Time::DAY, $time->toDays());
		Assert::same($seconds / Time::WEEK, $time->toWeeks());


		$timeNegative = Time::fromSeconds(-1 * $seconds);
		Assert::same(FALSE, $timeNegative->isNULL());
		Assert::same(FALSE, $timeNegative->isZero());
		Assert::same(18, $timeNegative->getSeconds());
		Assert::same(54, $timeNegative->getMinutes());
		Assert::same(6873, $timeNegative->getHours());
		Assert::same(NULL, $timeNegative->getSecondFraction());
		Assert::same(TRUE, $timeNegative->isNegative());
		Assert::same(-1, $timeNegative->getSignum());

		Assert::same(-1 * $seconds, $timeNegative->toSeconds());
		Assert::same(-1 * $seconds / Time::MINUTE, $timeNegative->toMinutes());
		Assert::same(-1 * $seconds / Time::HOUR, $timeNegative->toHours());
		Assert::same(-1 * $seconds / Time::DAY, $timeNegative->toDays());
		Assert::same(-1 * $seconds / Time::WEEK, $timeNegative->toWeeks());


		$timeFloat = Time::fromSeconds(0.376);
		Assert::same(FALSE, $timeFloat->isNULL());
		Assert::same(FALSE, $timeFloat->isZero());
		Assert::same(0, $timeFloat->getSeconds());
		Assert::same(0, $timeFloat->getMinutes());
		Assert::same(0, $timeFloat->getHours());
		Assert::same(0.376, $timeFloat->getSecondFraction());
		Assert::same(FALSE, $timeFloat->isNegative());
		Assert::same(1, $timeFloat->getSignum());

		Assert::same(.376, $timeFloat->toSeconds());
		Assert::same(.376 / Time::MINUTE, $timeFloat->toMinutes());
		Assert::same(.376 / Time::HOUR, $timeFloat->toHours());
		Assert::same(.376 / Time::DAY, $timeFloat->toDays());
		Assert::same(.376 / Time::WEEK, $timeFloat->toWeeks());
	}


	public function testArithmeticFunctions()
	{
		//NOTE: all the modifications are accumulated into the Time object instance.

		$t = Time::fromSeconds(10);
		// add
		Assert::same(12, $t->add(2)->toSeconds());
		Assert::same(8, $t->add(-2)->toSeconds());
		// sub
		Assert::same(8, $t->sub(Time::fromSeconds(2))->toSeconds());
		Assert::same(10, $t->sub(Time::fromSeconds(0))->toSeconds());
		Assert::same(-10, $t->sub(Time::fromSeconds(20))->toSeconds());
		// mult
		Assert::same(20, $t->mult(2)->toSeconds());
		Assert::same(-20, $t->mult(-2)->toSeconds());
		// div
		Assert::same(5, $t->div(2)->toSeconds());
		Assert::same(-5, $t->div(-2)->toSeconds());
		// mod
		Assert::same(0, $t->mod(2)->toSeconds());
		Assert::same(1, $t->mod(3)->toSeconds());
		Assert::same(2, $t->mod(4)->toSeconds());
		Assert::same(-1 % 4, Time::fromSeconds(-1)->mod(4)->toSeconds());


		// a more general test follows
		$vals = [0, 1, -1, 0.01, 954676.35435, -53456123.25667,];
		$bases = [NULL, 0, 1, -100, -1, 5.4, -3.7];
		foreach ($bases as $base) {
			$time = new Time($base);
			foreach ($vals as $val) {
				Assert::same($base + $val, $time->copy()->add($val)->getRaw());
				Assert::same($base - $val, $time->copy()->sub($val)->getRaw());
				Assert::same($base * $val, $time->copy()->mult($val)->getRaw());
				Assert::same($val != 0 ? $base / $val : INF, $time->copy()->div($val)->getRaw());
				if ($val != 0) {
					$real = $time->copy()->mod($val)->getRaw();
					$expected = is_int($val) && is_int($base) ? $base % $val : fmod($base, $val);
					Assert::same($expected, $real);
				} else {
					Assert::nan($time->copy()->mod($val)->getRaw());
				}
			}
		}

		//Note: when a Time instance contains NAN or INF, all the following manipulations with the instance result in NAN or INF
	}


	public function testAlterations()
	{
		$t = Time::create();

		// add
		Assert::same(2, $t->addSeconds(2)->toSeconds());
		Assert::same(2, $t->addMinutes(2)->toMinutes());
		Assert::same(2, $t->addHours(2)->toHours());
		Assert::same(2, $t->addDays(2)->toDays());
		Assert::same(2, $t->addWeeks(2)->toWeeks());

		// sub
		Assert::same(-2, $t->subSeconds(2)->toSeconds());
		Assert::same(-2, $t->subMinutes(2)->toMinutes());
		Assert::same(-2, $t->subHours(2)->toHours());
		Assert::same(-2, $t->subDays(2)->toDays());
		Assert::same(-2, $t->subWeeks(2)->toWeeks());

		// negatives
		Assert::same(-2, $t->addSeconds(-2)->toSeconds());
		Assert::same(2, $t->subSeconds(-2)->toSeconds());

		// clipping
		Assert::same(Time::DAY - 1, Time::fromSeconds(-1)->clipToDayTime()->getRaw());
		Assert::same(Time::DAY - 0.1, Time::fromSeconds(-0.1)->clipToDayTime()->getRaw());
		Assert::same(0, Time::fromSeconds(Time::DAY)->clipToDayTime()->toSeconds());
		Assert::same(1, Time::fromSeconds(1)->clipToDayTime()->toSeconds());
	}


	public function testComparisonFunctions()
	{
		$t1 = Time::fromSeconds(-1);
		$t2 = Time::fromSeconds(0);
		$t3 = Time::fromSeconds(1);

		// equal
		Assert::same(TRUE, $t1->eq($t1));
		Assert::same(FALSE, $t1->eq($t2));
		Assert::same(FALSE, $t1->eq($t3));

		// not equal
		Assert::same(FALSE, $t1->neq($t1));
		Assert::same(TRUE, $t1->neq($t2));
		Assert::same(TRUE, $t1->neq($t3));

		// less than
		Assert::same(FALSE, $t1->lt($t1));
		Assert::same(TRUE, $t1->lt($t2));
		Assert::same(TRUE, $t1->lt($t3));
		Assert::same(FALSE, $t2->lt($t1));
		Assert::same(FALSE, $t2->lt($t2));
		Assert::same(TRUE, $t2->lt($t3));

		// greater than
		Assert::same(FALSE, $t1->gt($t1));
		Assert::same(FALSE, $t1->gt($t2));
		Assert::same(FALSE, $t1->gt($t3));
		Assert::same(TRUE, $t2->gt($t1));
		Assert::same(FALSE, $t2->gt($t2));
		Assert::same(FALSE, $t2->gt($t3));

		// less than or equal
		Assert::same(TRUE, $t1->lte($t1));
		Assert::same(TRUE, $t1->lte($t2));
		Assert::same(TRUE, $t1->lte($t3));
		Assert::same(FALSE, $t2->lte($t1));
		Assert::same(TRUE, $t2->lte($t2));
		Assert::same(TRUE, $t2->lte($t3));

		// greater than or equal
		Assert::same(TRUE, $t1->gte($t1));
		Assert::same(FALSE, $t1->gte($t2));
		Assert::same(FALSE, $t1->gte($t3));
		Assert::same(TRUE, $t2->gte($t1));
		Assert::same(TRUE, $t2->gte($t2));
		Assert::same(FALSE, $t2->gte($t3));

		// between
		Assert::same(FALSE, $t1->between($t2, $t3));
		Assert::same(TRUE, $t2->between($t1, $t3));
		Assert::same(TRUE, $t2->between($t3, $t1));
		Assert::same(FALSE, $t3->between($t1, $t2));
		Assert::same(TRUE, $t1->between($t1, $t1)); // using <= and >= operators
		Assert::same(FALSE, $t1->between($t1, $t1, TRUE)); // using < and > operators
	}


	public function testTimeFunctions()
	{
		// is valid day time
		Assert::same(TRUE, Time::fromSeconds(0)->isValidDayTime());
		Assert::same(TRUE, Time::fromSeconds(1)->isValidDayTime());
		Assert::same(FALSE, Time::fromSeconds(Time::DAY)->isValidDayTime());
		Assert::same(TRUE, Time::fromSeconds(Time::DAY - 1)->isValidDayTime());
		Assert::same(FALSE, Time::fromSeconds(-1)->isValidDayTime());
		Assert::same(TRUE, Time::fromSeconds(-1)->clipToDayTime()->isValidDayTime());
		Assert::same(TRUE, Time::fromSeconds(Time::DAY)->clipToDayTime()->isValidDayTime());
	}


	public function testToCarbon()
	{
		$time = Time::fromSeconds(3723)->toCarbon();
		Assert::type(Carbon::CLASS, $time);
		Assert::same('01:02:03', $time->format('H:i:s'));
	}


	public function testToDateTime()
	{
		$time = Time::fromSeconds(3723)->toDateTime();
		Assert::type(DateTime::CLASS, $time);
		Assert::same('01:02:03', $time->format('H:i:s'));
	}


	/**
	 * This method tests the behaviour of methods that modify the object.
	 *
	 * @note: only setter methods are tested, as getters and comparators are not supposed to modify the object at all.
	 */
	public function testMutability()
	{
		// while using Time (which is immutable), any time modification results in a new instance of Time returned
		$immutable = new Time(0);
		$this->mutabilityObjectTest($immutable, FALSE);
		
		// to accumulate / change value one needs to use TimeMutable - mutable time object.
		// calling add(), set() or any other modifying method actually returns the modified $mutable TimeMutable instance,
		// so the result and the original are identical
		$mutable = new TimeMutable(0);
		$this->mutabilityObjectTest($mutable, TRUE);

	}


	//--------------------------------------------------------------------------
	//----------------------- Aux methods --------------------------------------


	private function mutabilityObjectTest(Time $timeObject, $expectedResult)
	{
		// arithmetic operations
		Assert::same($expectedResult, $timeObject === $timeObject->add(1));
		Assert::same($expectedResult, $timeObject === $timeObject->sub(1));
		Assert::same($expectedResult, $timeObject === $timeObject->mult(1));
		Assert::same($expectedResult, $timeObject === $timeObject->div(1));
		Assert::same($expectedResult, $timeObject === $timeObject->mod(1));

		// additions
		Assert::same($expectedResult, $timeObject === $timeObject->addSeconds(1));
		Assert::same($expectedResult, $timeObject === $timeObject->addMinutes(1));
		Assert::same($expectedResult, $timeObject === $timeObject->addHours(1));
		Assert::same($expectedResult, $timeObject === $timeObject->addDays(1));
		Assert::same($expectedResult, $timeObject === $timeObject->addWeeks(1));
		Assert::same($expectedResult, $timeObject === $timeObject->subSeconds(1));
		Assert::same($expectedResult, $timeObject === $timeObject->subMinutes(1));
		Assert::same($expectedResult, $timeObject === $timeObject->subHours(1));
		Assert::same($expectedResult, $timeObject === $timeObject->subDays(1));
		Assert::same($expectedResult, $timeObject === $timeObject->subWeeks(1));

		// clipping
		Assert::same($expectedResult, $timeObject === $timeObject->clipToDayTime());
	}

}

// run the test
(new TimeTest)->run();


