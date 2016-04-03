<?php

/**
 * @author Andrej Rypak <xrypak@gmail.com>
 */


namespace Dakujem\Test\Time;

require_once __DIR__ . '/bootstrap.php';

use Carbon\Carbon,
	Dakujem\Time,
	Dakujem\TimeFactory,
	Dakujem\TimeImmutable,
	DateTime,
	Tester,
	Tester\Assert;


class TimeTest extends Tester\TestCase
{


	protected function setUp()
	{
		parent::setUp();
	}


	protected function tearDown()
	{
		parent::tearDown();
	}


	public function testConstants()
	{
		// time constants
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

		// format constants
		Assert::same('?H:i:s', Time::FORMAT_HMS);
		Assert::same('+H:i:s', Time::FORMAT_HMS_SIGNED);
		Assert::same('?H:i', Time::FORMAT_HM);
		Assert::same('+H:i', Time::FORMAT_HM_SIGNED);
		Assert::same('h:i:s A', Time::FORMAT_HMSA);
		Assert::same('h:i A', Time::FORMAT_HMA);
	}


	public function testFactories()
	{
		$seconds = 14;
		$format = Time::FORMAT_HMS;

		// constructor
		Assert::same($seconds, (new Time($seconds))->toSeconds());
		Assert::same($seconds, (new Time($seconds, $format))->toSeconds());
		Assert::same($seconds, (new Time('00:00:' . $seconds, $format))->toSeconds());

		// universal factory
		Assert::same($seconds, Time::create($seconds)->toSeconds());

		// from *
		Assert::same($seconds, Time::fromSeconds($seconds)->toSeconds());

		// copy
		Assert::same($seconds, Time::create($seconds)->copy()->toSeconds());

		// time factory
		Assert::same($seconds, (new TimeFactory)->create($seconds)->toSeconds());

		//TODO ->set()
	}


	public function testGetters()
	{
		$timeZero = Time::fromSeconds(0);
		Assert::same(TRUE, $timeZero->isZero());
		Assert::same(0, $timeZero->getSeconds());
		Assert::same(0, $timeZero->getMinutes());
		Assert::same(0, $timeZero->getHours());
		Assert::same(FALSE, $timeZero->isNegative());

		Assert::same(0, $timeZero->toSeconds());
		Assert::same(0, $timeZero->toMinutes());
		Assert::same(0, $timeZero->toHours());
		Assert::same(0, $timeZero->getSignum());


		$seconds = 6873 * 3600 + 54 * 60 + 18; // 6873 hours 54 minutes 18 seconds

		$time = Time::fromSeconds($seconds);
		Assert::same(FALSE, $time->isZero());
		Assert::same(18, $time->getSeconds());
		Assert::same(54, $time->getMinutes());
		Assert::same(6873, $time->getHours());
		Assert::same(FALSE, $time->isNegative());
		Assert::same(1, $time->getSignum());

		Assert::same($seconds, $time->toSeconds());
		Assert::same($seconds / 60, $time->toMinutes());
		Assert::same($seconds / 60 / 60, $time->toHours());


		$timeNegative = Time::fromSeconds(-1 * $seconds);
		Assert::same(FALSE, $timeNegative->isZero());
		Assert::same(18, $timeNegative->getSeconds());
		Assert::same(54, $timeNegative->getMinutes());
		Assert::same(6873, $timeNegative->getHours());
		Assert::same(TRUE, $timeNegative->isNegative());
		Assert::same(-1, $timeNegative->getSignum());

		Assert::same(-1 * $seconds, $timeNegative->toSeconds());
		Assert::same(-1 * $seconds / 60, $timeNegative->toMinutes());
		Assert::same(-1 * $seconds / 60 / 60, $timeNegative->toHours());


		$nullTime = (new Time);
		Assert::same(FALSE, $timeZero->isNULL());
		Assert::same(FALSE, $timeNegative->isNULL());
		Assert::same(FALSE, $time->isNULL());
		Assert::same(TRUE, $nullTime->isNull());
		Assert::same(FALSE, $nullTime->isZero());
	}


	public function testTimeFunctions()
	{
		// is valid day time
		Assert::same(TRUE, Time::fromSeconds(0)->isValidDayTime());
		Assert::same(TRUE, Time::fromSeconds(1)->isValidDayTime());
		Assert::same(FALSE, Time::fromSeconds(Time::DAY)->isValidDayTime());
		Assert::same(TRUE, Time::fromSeconds(Time::DAY - 1)->isValidDayTime());
		Assert::same(FALSE, Time::fromSeconds(-1)->isValidDayTime());

		// clip to day time
		Assert::same(TRUE, Time::fromSeconds(-1)->clipToDayTime()->isValidDayTime());
		Assert::same(TRUE, Time::fromSeconds(Time::DAY)->clipToDayTime()->isValidDayTime());
		Assert::same(Time::DAY - 1, Time::fromSeconds(-1)->clipToDayTime()->toSeconds());
		Assert::same(0, Time::fromSeconds(Time::DAY)->clipToDayTime()->toSeconds());
	}


	public function testArithmeticFunctions()
	{
		$t = Time::fromSeconds(6);
		// add
		Assert::same(12, $t->add($t)->toSeconds()); // 6+6
		// sub
		Assert::same(10, $t->sub(Time::fromSeconds(2))->toSeconds()); // 12-2
		Assert::same(-10, $t->sub(Time::fromSeconds(20))->toSeconds()); // 10-20
		// mult
		Assert::same(-20, $t->mult(2)->toSeconds()); // -10 * 2
		// div
		Assert::same(-10, $t->div(2)->toSeconds()); // -20 / 2
		// mod
		Assert::same(0, $t->mod(2)->toSeconds()); // -10 % 2
		$t->add(Time::fromSeconds(6)); // 0+6
		Assert::same(2, $t->mod(4)->toSeconds()); // 6 % 4
		Assert::same(-1 % 4, Time::fromSeconds(-1)->mod(4)->toSeconds());
	}


	public function testAlterations()
	{
		$t = Time::create();

		Assert::same(2, $t->copy()->addSeconds(2)->toSeconds());
		Assert::same(2, $t->copy()->addMinutes(2)->toMinutes());
		Assert::same(2, $t->copy()->addHours(2)->toHours());
		Assert::same(2, $t->copy()->addDays(2)->toDays());
		Assert::same(2, $t->copy()->addWeeks(2)->toWeeks());

		Assert::same(-2, $t->copy()->subSeconds(2)->toSeconds());
		Assert::same(-2, $t->copy()->subMinutes(2)->toMinutes());
		Assert::same(-2, $t->copy()->subHours(2)->toHours());
		Assert::same(-2, $t->copy()->subDays(2)->toDays());
		Assert::same(-2, $t->copy()->subWeeks(2)->toWeeks());

		Assert::same(-2, $t->copy()->addSeconds(-2)->toSeconds());
		Assert::same(2, $t->copy()->subSeconds(-2)->toSeconds());
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


	public function testFormatting()
	{
		// default format is '?H:i:s'
		Assert::same(Time::FORMAT_HMS, (new Time)->getFormat());
		Assert::same('00:00:00', (string) new Time); // uninitialized time
		Assert::same('00:00:00', (string) Time::fromSeconds(0));
		Assert::same('00:01:00', (string) Time::fromSeconds(60));
		Assert::same('00:01:40', (string) Time::fromSeconds(100));
		Assert::same('00:01:41', (string) Time::fromSeconds(101));
		Assert::same('01:00:00', (string) Time::fromSeconds(3600));
		Assert::same('01:01:40', (string) Time::fromSeconds(3700));
		Assert::same('01:01:41', (string) Time::fromSeconds(3701));

		// not valid day time
		Assert::same('-00:00:01', (string) Time::fromSeconds(-1));
		Assert::same('24:00:00', (string) Time::fromSeconds(Time::DAY));

		// format with persistent signum
		$plusFormat = Time::FORMAT_HMS_SIGNED;
		Assert::same('+00:00:00', (string) Time::fromSeconds(0)->setFormat($plusFormat));
		Assert::same('+00:00:01', (string) Time::fromSeconds(1)->setFormat($plusFormat));
		Assert::same('-00:00:01', (string) Time::fromSeconds(-1)->setFormat($plusFormat));

		// AM / PM
		Assert::same('12:00:00 AM', (string) Time::fromSeconds(0)->setFormat(Time::FORMAT_HMSA)); // midnight
		Assert::same('12:00:00 PM', (string) Time::fromSeconds(12 * 60 * 60)->setFormat(Time::FORMAT_HMSA)); // noon / midday
		Assert::same('11:59:59 AM', (string) Time::fromSeconds(12 * 60 * 60 - 1)->setFormat(Time::FORMAT_HMSA));
		Assert::same('11:59:59 PM', (string) Time::fromSeconds(24 * 60 * 60 - 1)->setFormat(Time::FORMAT_HMSA));
		Assert::same('12:00:01 PM', (string) Time::fromSeconds(12 * 60 * 60 + 1)->setFormat(Time::FORMAT_HMSA));
		Assert::same('12:00:01 AM', (string) Time::fromSeconds(1)->setFormat(Time::FORMAT_HMSA));
		//NOTE: there are no tests for invalid day time with 12h format as the behaviour is undefined
	}


	public function testParsing()
	{
		// valid 24-hour format time
		Assert::same('12:34:56', (string) Time::create('12:34:56'));
		Assert::same(45296, Time::create('12:34:56')->toSeconds());
		Assert::same(300, Time::create('00:05')->toSeconds());
		Assert::same(300, Time::create('0:5')->toSeconds());
		Assert::same(5, Time::create('00:00:05')->toSeconds());
		Assert::same(5, Time::create('0:0:5')->toSeconds());

		// 12-hour time
		Assert::same(45000, Time::create('12:30 PM', Time::FORMAT_HMSA)->toSeconds());
		Assert::same(1800, Time::create('12:30 AM', Time::FORMAT_HMSA)->toSeconds());
		Assert::same(45000, Time::create('PM 12:30', Time::FORMAT_HMSA)->toSeconds());
		Assert::same(1800, Time::create('AM 12:30', Time::FORMAT_HMSA)->toSeconds());
		Assert::same(41404, Time::create('AM 11:30:04', Time::FORMAT_HMSA)->toSeconds());
		Assert::same(TRUE, Time::create('13:30 AM', Time::FORMAT_HMSA)->isNULL()); // not a valid 12-h time
		Assert::same(TRUE, Time::create('-1:30 AM', Time::FORMAT_HMSA)->isNULL()); // not a valid 12-h time
		/**/
		// not valid day times
		Assert::same(123 * Time::HOUR + 34 * Time::MINUTE, Time::create('123:34')->toSeconds());
		Assert::same(123 * Time::HOUR + 34 * Time::MINUTE + 56, Time::create('123:34:56')->toSeconds());
		Assert::same(-1 * Time::HOUR - 30 * Time::MINUTE, Time::create('-1:30', '?H:i')->toSeconds());
		Assert::same(-1 * Time::HOUR + 30 * Time::MINUTE, Time::create('-1:30', '?H:?i')->toSeconds()); // the sign must be before hours and minutes
		Assert::same(-1 * Time::HOUR - 30 * Time::MINUTE, Time::create('-1:-30', '?H:?i')->toSeconds()); // now the reading is as (probably) expected above
		Assert::same(1 * Time::HOUR - 30 * Time::MINUTE, Time::create('1:-30', '?H:?i')->toSeconds());
		Assert::same(-1, Time::create('-0:00:01')->toSeconds());
		Assert::same(-61, Time::create('-0:01:-01')->toSeconds());
//		Assert::same(-1, Time::create('-0:01', '?i:s')->toSeconds()); //TODO fix this !
		Assert::same(-123 * Time::HOUR - 34 * Time::MINUTE - 12, Time::create('-123:34:12')->toSeconds());
		Assert::same(-123 * Time::HOUR - 34 * Time::MINUTE - 12, Time::create('-123:-34:+12')->toSeconds()); // only the hour sign matters here!
		Assert::same(123 * Time::HOUR + 34 * Time::MINUTE + 12, Time::create('+123:-34:+12')->toSeconds()); // only the hour sign matters here!
		Assert::same(-123 * Time::HOUR - 34 * Time::MINUTE + 12, Time::create('-123:-34:+12', '?H:?i:?s')->toSeconds()); // every sign matters
		/**/
		// strange formats
		Assert::same(-1 * Time::HOUR + 30, Time::create('-1:30', '?H:?s')->toSeconds());
		Assert::same(-1 * Time::HOUR - 30, Time::create('-1:30', 'H:s')->toSeconds());
		Assert::same(2 * Time::HOUR + 1, Time::create('1:2', 's:H')->toSeconds());
		Assert::same(2 * Time::MINUTE + 1, Time::create('1:2', 's:i')->toSeconds());
		Assert::same(1 * Time::HOUR + 2 * Time::MINUTE + 3, Time::create('3:2:1', 's:i:H')->toSeconds());
		Assert::same(1 * Time::HOUR + 3 * Time::MINUTE + 2, Time::create('3:2:1', 'i:s:H')->toSeconds());
		Assert::same(1 * Time::HOUR, Time::create('1:2:3', 'H')->toSeconds());
		Assert::same(1 * Time::MINUTE, Time::create('1:2:3', 'i')->toSeconds());
		Assert::same(1, Time::create('1:2:3', 's')->toSeconds());

		// test Carbon
		Assert::same(1 * Time::HOUR + 2 * Time::MINUTE + 3, Time::create(Carbon::createFromFormat('H:i:s', '1:02:03'))->toSeconds());
		Assert::same(0 * Time::HOUR + 2 * Time::MINUTE + 3, Time::create(Carbon::createFromFormat('H:i:s', '24:02:03'))->toSeconds()); // this results in 00:02:03 the next day in Carbon
		/**/
		// test DateTime
		Assert::same(1 * Time::HOUR + 2 * Time::MINUTE + 3, Time::create(new DateTime('1:02:03'))->toSeconds());
		Assert::same(0 * Time::HOUR + 2 * Time::MINUTE + 3, Time::create(new DateTime('24:02:03'))->toSeconds());
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
		// calling add(), set() or any other method actually returns the modified $mutable Time instance,
		// so the result and the original are identical
		$mutable = new Time(0);
		$this->mutabilityObjectTest($mutable, TRUE);

		// while using TimeImmutable, however, any modification results in a new instance of TimeImmutable returned
		$immutable = new TimeImmutable(0);
		$this->mutabilityObjectTest($immutable, FALSE);
	}


	private function mutabilityObjectTest(Time $timeObject, $expectedResult)
	{
		// setting time value
		Assert::same($expectedResult, $timeObject === $timeObject->set(0));

		// setting format
		Assert::same($expectedResult, $timeObject === $timeObject->setFormat('foo'));

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


