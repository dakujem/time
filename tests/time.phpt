<?php

/**
 * This file is a part of dakujem/time package.
 * @author Andrej Rypak (dakujem) <xrypak@gmail.com>
 */


namespace Dakujem\Test\Time;

require_once __DIR__ . '/bootstrap.php';

use Carbon\Carbon,
	Dakujem\Time,
	Dakujem\TimeFactory,
	Dakujem\TimeImmutable,
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

		// format constants
		Assert::same('?H:i:s', Time::FORMAT_HMS);
		Assert::same('+H:i:s', Time::FORMAT_HMS_SIGNED);
		Assert::same('?H:i', Time::FORMAT_HM);
		Assert::same('+H:i', Time::FORMAT_HM_SIGNED);
		Assert::same('h:i:s A', Time::FORMAT_HMSA);
		Assert::same('h:i A', Time::FORMAT_HMA);
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
		Assert::same(FALSE, $uninitialized->isZero());
		Assert::same(0, $uninitialized->getSeconds());
		Assert::same(0, $uninitialized->getMinutes());
		Assert::same(0, $uninitialized->getHours());
		Assert::same(FALSE, $uninitialized->isNegative());

		Assert::same(0, $uninitialized->toSeconds());
		Assert::same(0, $uninitialized->toMinutes());
		Assert::same(0, $uninitialized->toHours());
		Assert::same(0, $uninitialized->getSignum());
		Assert::same(0, $uninitialized->toDays());
		Assert::same(0, $uninitialized->toWeeks());


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
		Assert::same(0, $timeZero->toDays());
		Assert::same(0, $timeZero->toWeeks());


		$seconds = 6873 * Time::HOUR + 54 * Time::MINUTE + 18; // 6873 hours 54 minutes 18 seconds

		$time = Time::fromSeconds($seconds);
		Assert::same(FALSE, $time->isZero());
		Assert::same(18, $time->getSeconds());
		Assert::same(54, $time->getMinutes());
		Assert::same(6873, $time->getHours());
		Assert::same(FALSE, $time->isNegative());
		Assert::same(1, $time->getSignum());

		Assert::same($seconds, $time->toSeconds());
		Assert::same($seconds / Time::MINUTE, $time->toMinutes());
		Assert::same($seconds / Time::HOUR, $time->toHours());
		Assert::same($seconds / Time::DAY, $time->toDays());
		Assert::same($seconds / Time::WEEK, $time->toWeeks());


		$timeNegative = Time::fromSeconds(-1 * $seconds);
		Assert::same(FALSE, $timeNegative->isZero());
		Assert::same(18, $timeNegative->getSeconds());
		Assert::same(54, $timeNegative->getMinutes());
		Assert::same(6873, $timeNegative->getHours());
		Assert::same(TRUE, $timeNegative->isNegative());
		Assert::same(-1, $timeNegative->getSignum());

		Assert::same(-1 * $seconds, $timeNegative->toSeconds());
		Assert::same(-1 * $seconds / Time::MINUTE, $timeNegative->toMinutes());
		Assert::same(-1 * $seconds / Time::HOUR, $timeNegative->toHours());
		Assert::same(-1 * $seconds / Time::DAY, $timeNegative->toDays());
		Assert::same(-1 * $seconds / Time::WEEK, $timeNegative->toWeeks());


		Assert::same(TRUE, $uninitialized->isNull());
		Assert::same(FALSE, $timeZero->isNULL());
		Assert::same(FALSE, $timeNegative->isNULL());
		Assert::same(FALSE, $time->isNULL());
	}


	public function testArithmeticFunctions()
	{
		//NOTE: all the modifications are accumulated into the Time object.

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

		// add
		Assert::same(2, $t->copy()->addSeconds(2)->toSeconds());
		Assert::same(2, $t->copy()->addMinutes(2)->toMinutes());
		Assert::same(2, $t->copy()->addHours(2)->toHours());
		Assert::same(2, $t->copy()->addDays(2)->toDays());
		Assert::same(2, $t->copy()->addWeeks(2)->toWeeks());

		// sub
		Assert::same(-2, $t->copy()->subSeconds(2)->toSeconds());
		Assert::same(-2, $t->copy()->subMinutes(2)->toMinutes());
		Assert::same(-2, $t->copy()->subHours(2)->toHours());
		Assert::same(-2, $t->copy()->subDays(2)->toDays());
		Assert::same(-2, $t->copy()->subWeeks(2)->toWeeks());

		// negatives
		Assert::same(-2, $t->copy()->addSeconds(-2)->toSeconds());
		Assert::same(2, $t->copy()->subSeconds(-2)->toSeconds());

		// clipping
		Assert::same(Time::DAY - 1, Time::fromSeconds(-1)->clipToDayTime()->toSeconds());
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
		Assert::same(123 * Time::HOUR + 34 * Time::MINUTE + 56 * Time::SECOND, Time::create('123:34:56')->toSeconds());
		Assert::same(-1 * Time::HOUR - 30 * Time::MINUTE, Time::create('-1:30', '?H:i')->toSeconds());
		Assert::same(-1 * Time::HOUR + 30 * Time::MINUTE, Time::create('-1:30', '?H:?i')->toSeconds()); // the sign must be before hours and minutes
		Assert::same(-1 * Time::HOUR - 30 * Time::MINUTE, Time::create('-1:-30', '?H:?i')->toSeconds()); // now the reading is as (probably) expected above
		Assert::same(1 * Time::HOUR - 30 * Time::MINUTE, Time::create('1:-30', '?H:?i')->toSeconds());
		Assert::same(-1 * Time::SECOND, Time::create('-0:00:01')->toSeconds());
		Assert::same(-61 * Time::SECOND, Time::create('-0:01:-01')->toSeconds());
//		Assert::same(-1 * Time::SECOND, Time::create('-0:01', '?i:s')->toSeconds()); //TODO fix this !
		Assert::same(-123 * Time::HOUR - 34 * Time::MINUTE - 12 * Time::SECOND, Time::create('-123:34:12')->toSeconds());
		Assert::same(-123 * Time::HOUR - 34 * Time::MINUTE - 12 * Time::SECOND, Time::create('-123:-34:+12')->toSeconds()); // only the hour sign matters here!
		Assert::same(123 * Time::HOUR + 34 * Time::MINUTE + 12 * Time::SECOND, Time::create('+123:-34:+12')->toSeconds()); // only the hour sign matters here!
		Assert::same(-123 * Time::HOUR - 34 * Time::MINUTE + 12 * Time::SECOND, Time::create('-123:-34:+12', '?H:?i:?s')->toSeconds()); // every sign matters
		/**/
		// strange formats
		Assert::same(-1 * Time::HOUR + 30 * Time::SECOND, Time::create('-1:30', '?H:?s')->toSeconds());
		Assert::same(-1 * Time::HOUR - 30 * Time::SECOND, Time::create('-1:30', 'H:s')->toSeconds());
		Assert::same(2 * Time::HOUR + 1 * Time::SECOND, Time::create('1:2', 's:H')->toSeconds());
		Assert::same(2 * Time::MINUTE + 1 * Time::SECOND, Time::create('1:2', 's:i')->toSeconds());
		Assert::same(1 * Time::HOUR + 2 * Time::MINUTE + 3 * Time::SECOND, Time::create('3:2:1', 's:i:H')->toSeconds());
		Assert::same(1 * Time::HOUR + 3 * Time::MINUTE + 2 * Time::SECOND, Time::create('3:2:1', 'i:s:H')->toSeconds());
		Assert::same(1 * Time::HOUR, Time::create('1:2:3', 'H')->toSeconds());
		Assert::same(1 * Time::MINUTE, Time::create('1:2:3', 'i')->toSeconds());
		Assert::same(1 * Time::SECOND, Time::create('1:2:3', 's')->toSeconds());

		// test Carbon
		$c1 = Carbon::createFromFormat('H:i:s', '1:02:03');
		Assert::same(1, $c1->hour);
		Assert::same(2, $c1->minute);
		Assert::same(3, $c1->second);
		$c2 = Carbon::createFromFormat('H:i:s', '24:02:03'); // this results in 00:02:03 the next day in Carbon
		Assert::same(0, $c2->hour);
		Assert::same(2, $c2->minute);
		Assert::same(3, $c2->second);
		Assert::same(1 * Time::HOUR + 2 * Time::MINUTE + 3 * Time::SECOND, Time::create($c1)->toSeconds());
		Assert::same(0 * Time::HOUR + 2 * Time::MINUTE + 3 * Time::SECOND, Time::create($c2)->toSeconds());

		// test DateTime
		Assert::same(1 * Time::HOUR + 2 * Time::MINUTE + 3 * Time::SECOND, Time::create(new DateTime('1:02:03'))->toSeconds());
		Assert::same(0 * Time::HOUR + 2 * Time::MINUTE + 3 * Time::SECOND, Time::create(new DateTime('24:02:03'))->toSeconds());

		// arrays
		Assert::same(TRUE, Time::create([])->isNull());
		Assert::same(FALSE, Time::create([])->isZero());
		Assert::same(0, Time::create([])->toSeconds());
		Assert::same(FALSE, Time::create(['foo', 'bar'])->isNull());
		Assert::same(TRUE, Time::create(['foo', 'bar'])->isZero());
		Assert::same(0, Time::create(['foo', 'bar'])->toSeconds());
		Assert::same(1 * Time::SECOND, Time::create([1])->toSeconds());
		Assert::same(1 * Time::HOUR + 2 * Time::MINUTE + 3 * Time::SECOND, Time::create([3, 2, 1])->toSeconds());
		Assert::same(1 * Time::WEEK + 2 * Time::DAY + 3 * Time::HOUR + 4 * Time::MINUTE + 5 * Time::SECOND, Time::create([5, 4, 3, 2, 1])->toSeconds());
		Assert::same(-1 * Time::WEEK - 2 * Time::DAY - 3 * Time::HOUR - 4 * Time::MINUTE - 5 * Time::SECOND, Time::create([-5, - 4, -3, - 2, - 1])->toSeconds());
		Assert::same(1 * Time::WEEK - 2 * Time::DAY + 3 * Time::HOUR - 4 * Time::MINUTE - 5 * Time::SECOND, Time::create([-5, - 4, 3, - 2, 1, 'foo', 78554])->toSeconds());

		// null, empty string
		Assert::same(TRUE, Time::create(NULL)->isNull());
		Assert::same(FALSE, Time::create(NULL)->isZero());
		Assert::same(TRUE, Time::create('')->isNull());
		Assert::same(FALSE, Time::create('')->isZero());
	}


	public function testFactories()
	{
		// Note: the raw getter is not intended for time getting outside the test environment

		$testValues = [
			[
				'input' => 14,
				'format' => NULL,
				'expected' => 14,
			],
			[
				'input' => '12:30:45',
				'format' => Time::FORMAT_HMS,
				'expected' => 12 * Time::HOUR + 30 * Time::MINUTE + 45 * Time::SECOND,
			],
			[
				'input' => '-00:00:01',
				'format' => Time::FORMAT_HMS,
				'expected' => -1 * Time::SECOND,
			],
			[
				'input' => NULL,
				'format' => Time::FORMAT_HMS,
				'expected' => NULL,
			],
		];

		// constructor
		foreach ($testValues as $v) {
			Assert::same($v['expected'], (new Time($v['input'], $v['format']))->getRaw());
		}

		// universal factory
		foreach ($testValues as $v) {
			Assert::same($v['expected'], Time::create($v['input'], $v['format'])->getRaw());
		}

		// ->set()
		foreach ($testValues as $v) {
			$t = new Time;
			Assert::same($v['expected'], $t->set($v['input'], $v['format'])->getRaw());
		}

		// time factory
		foreach ($testValues as $v) {
			$tf1 = new TimeFactory($v['format'], FALSE);
			$t1 = $tf1->create($v['input']);
			Assert::type(Time::CLASS, $t1);
			Assert::same($v['expected'], $t1->getRaw());
		}
		foreach ($testValues as $v) {
			$tf2 = new TimeFactory;
			$t2 = $tf2->create($v['input'], $v['format']);
			Assert::type(Time::CLASS, $t2);
			Assert::same($v['expected'], $t2->getRaw());
		}

		// from *
		$this->factoryTest('Seconds', Time::SECOND);
		$this->factoryTest('Minutes', Time::MINUTE);
		$this->factoryTest('Hours', Time::HOUR);
		$this->factoryTest('Days', Time::DAY);
		$this->factoryTest('Weeks', Time::WEEK);
		Assert::same(2 * Time::WEEK + 3 * Time::DAY + 4 * Time::HOUR + 5 * Time::MINUTE + 6 * Time::SECOND, Time::fromWeeks(2, 3, 4, 5, 6)->getRaw());
		Assert::same(-2 * Time::WEEK - 3 * Time::DAY - 4 * Time::HOUR - 5 * Time::MINUTE - 6 * Time::SECOND, Time::fromWeeks(-2, -3, -4, -5, -6)->getRaw());
		Assert::same(0, Time::fromWeeks(0, 0, 0, 0, 0)->getRaw());
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


	//--------------------------------------------------------------------------
	//----------------------- Aux methods --------------------------------------


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


	private function factoryTest($factoryMethodSuffix, $coefficient)
	{
		Assert::same(14 * $coefficient, Time::{'from' . $factoryMethodSuffix}(14)->getRaw());
		Assert::same(13 * $coefficient, Time::{'from' . $factoryMethodSuffix}(13)->getRaw());
		Assert::same(-1 * $coefficient, Time::{'from' . $factoryMethodSuffix}(-1)->getRaw());
		Assert::same(0 * $coefficient, Time::{'from' . $factoryMethodSuffix}(0)->getRaw());
	}

}

// run the test
(new TimeTest)->run();


