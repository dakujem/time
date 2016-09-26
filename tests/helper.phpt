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
	Dakujem\TimeHelper,
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
class TimeHelperTest extends TestCase
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


	public function testFoo()
	{

	}


	public function testConstants()
	{
		// format constants
		Assert::same('?H:i:s', TimeHelper::FORMAT_HMS);
		Assert::same('+H:i:s', TimeHelper::FORMAT_HMS_SIGNED);
		Assert::same('?H:i', TimeHelper::FORMAT_HM);
		Assert::same('+H:i', TimeHelper::FORMAT_HM_SIGNED);
		Assert::same('h:i:s A', TimeHelper::FORMAT_HMSA);
		Assert::same('h:i A', TimeHelper::FORMAT_HMA);
	}


	public function testFactory()
	{
		Assert::same(TimeHelper::FORMAT_HMS, (new TimeFactory())->getFormat());
	}


	public function testFormatting()
	{
		// default format is '?H:i:s'
		Assert::same(TimeHelper::FORMAT_HMS, TimeHelper::$defaultFormat);

		Assert::same('00:00:00', (string) new Time); // uninitialized time
		Assert::same('00:00:00', TimeHelper::format(Time::fromSeconds(0)));
		Assert::same('00:01:00', TimeHelper::format(Time::fromSeconds(60)));
		Assert::same('00:01:40', TimeHelper::format(Time::fromSeconds(100)));
		Assert::same('00:01:41', TimeHelper::format(Time::fromSeconds(101)));
		Assert::same('01:00:00', TimeHelper::format(Time::fromSeconds(3600)));
		Assert::same('01:01:40', TimeHelper::format(Time::fromSeconds(3700)));
		Assert::same('01:01:41', TimeHelper::format(Time::fromSeconds(3701)));

		// not valid day time
		Assert::same('-00:00:01', TimeHelper::format(Time::fromSeconds(-1)));
		Assert::same('24:00:00', TimeHelper::format(Time::fromSeconds(Time::DAY)));

		// format with persistent signum
		$plusFormat = TimeHelper::FORMAT_HMS_SIGNED;
		Assert::same('+00:00:00', TimeHelper::format(Time::fromSeconds(0), $plusFormat));
		Assert::same('+00:00:01', TimeHelper::format(Time::fromSeconds(1), $plusFormat));
		Assert::same('-00:00:01', TimeHelper::format(Time::fromSeconds(-1), $plusFormat));

		// AM / PM
		$format12h = TimeHelper::FORMAT_HMSA;
		Assert::same('12:00:00 AM', TimeHelper::format(Time::fromSeconds(0), $format12h)); // midnight
		Assert::same('12:00:00 PM', TimeHelper::format(Time::fromSeconds(12 * 60 * 60), $format12h)); // noon / midday
		Assert::same('11:59:59 AM', TimeHelper::format(Time::fromSeconds(12 * 60 * 60 - 1), $format12h));
		Assert::same('11:59:59 PM', TimeHelper::format(Time::fromSeconds(24 * 60 * 60 - 1), $format12h));
		Assert::same('12:00:01 PM', TimeHelper::format(Time::fromSeconds(12 * 60 * 60 + 1), $format12h));
		Assert::same('12:00:01 AM', TimeHelper::format(Time::fromSeconds(1), $format12h));

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
		$format12h = TimeHelper::FORMAT_HMSA;
		Assert::same(45000, TimeHelper::parse('12:30 PM', $format12h));
		Assert::same(45000, Time::create(TimeHelper::parse('12:30 PM', $format12h))->toSeconds());
		Assert::same(1800, Time::create(TimeHelper::parse('12:30 AM', $format12h))->toSeconds());
		Assert::same(45000, Time::create(TimeHelper::parse('PM 12:30', $format12h))->toSeconds());
		Assert::same(1800, Time::create(TimeHelper::parse('AM 12:30', $format12h))->toSeconds());
		Assert::same(41404, Time::create(TimeHelper::parse('AM 11:30:04', $format12h))->toSeconds());
		Assert::same(TRUE, Time::create(TimeHelper::parse('13:30 AM', $format12h))->isNULL()); // not a valid 12-h time
		Assert::same(TRUE, Time::create(TimeHelper::parse('-1:30 AM', $format12h))->isNULL()); // not a valid 12-h time
		// ------------ potialto OK




		/**/
		// not valid day times
		Assert::same(123 * Time::HOUR + 34 * Time::MINUTE, TimeHelper::parse('123:34'));
		Assert::same(123 * Time::HOUR + 34 * Time::MINUTE + 56 * Time::SECOND, TimeHelper::parse('123:34:56'));
		Assert::same(-1 * Time::HOUR - 30 * Time::MINUTE, TimeHelper::parse('-1:30', '?H:i'));
		Assert::same(-1 * Time::HOUR + 30 * Time::MINUTE, TimeHelper::parse('-1:30', '?H:?i')); // the sign must be before hours and minutes
		Assert::same(-1 * Time::HOUR - 30 * Time::MINUTE, TimeHelper::parse('-1:-30', '?H:?i')); // now the reading is as (probably) expected above
		Assert::same(1 * Time::HOUR - 30 * Time::MINUTE, TimeHelper::parse('1:-30', '?H:?i'));
		Assert::same(-1 * Time::SECOND, TimeHelper::parse('-0:00:01'));
		Assert::same(-61 * Time::SECOND, TimeHelper::parse('-0:01:-01'));
//		Assert::same(-1 * Time::SECOND, TimeHelper::parse('-0:01', '?i:s')); // NOTE: fix this !
		Assert::same(-123 * Time::HOUR - 34 * Time::MINUTE - 12 * Time::SECOND, TimeHelper::parse('-123:34:12'));
		Assert::same(-123 * Time::HOUR - 34 * Time::MINUTE - 12 * Time::SECOND, TimeHelper::parse('-123:-34:+12')); // only the hour sign matters here!
		Assert::same(123 * Time::HOUR + 34 * Time::MINUTE + 12 * Time::SECOND, TimeHelper::parse('+123:-34:+12')); // only the hour sign matters here!
		Assert::same(-123 * Time::HOUR - 34 * Time::MINUTE + 12 * Time::SECOND, TimeHelper::parse('-123:-34:+12', '?H:?i:?s')); // every sign matters
		/**/
		// strange formats
		Assert::same(-1 * Time::HOUR + 30 * Time::SECOND, TimeHelper::parse('-1:30', '?H:?s'));
		Assert::same(-1 * Time::HOUR - 30 * Time::SECOND, TimeHelper::parse('-1:30', 'H:s'));
		Assert::same(2 * Time::HOUR + 1 * Time::SECOND, TimeHelper::parse('1:2', 's:H'));
		Assert::same(2 * Time::MINUTE + 1 * Time::SECOND, TimeHelper::parse('1:2', 's:i'));
		Assert::same(1 * Time::HOUR + 2 * Time::MINUTE + 3 * Time::SECOND, TimeHelper::parse('3:2:1', 's:i:H'));
		Assert::same(1 * Time::HOUR + 3 * Time::MINUTE + 2 * Time::SECOND, TimeHelper::parse('3:2:1', 'i:s:H'));
		Assert::same(1 * Time::HOUR, TimeHelper::parse('1:2:3', 'H'));
		Assert::same(1 * Time::MINUTE, TimeHelper::parse('1:2:3', 'i'));
		Assert::same(1 * Time::SECOND, TimeHelper::parse('1:2:3', 's'));

		// test Carbon
		$c1 = Carbon::createFromFormat('H:i:s', '1:02:03');
		Assert::same(1, $c1->hour);
		Assert::same(2, $c1->minute);
		Assert::same(3, $c1->second);
		$c2 = Carbon::createFromFormat('H:i:s', '24:02:03'); // this results in 00:02:03 the next day in Carbon
		Assert::same(0, $c2->hour);
		Assert::same(2, $c2->minute);
		Assert::same(3, $c2->second);
		Assert::same(1 * Time::HOUR + 2 * Time::MINUTE + 3 * Time::SECOND, TimeHelper::parse($c1));
		Assert::same(0 * Time::HOUR + 2 * Time::MINUTE + 3 * Time::SECOND, TimeHelper::parse($c2));

		// test DateTime
		Assert::same(1 * Time::HOUR + 2 * Time::MINUTE + 3 * Time::SECOND, TimeHelper::parse(new DateTime('1:02:03')));
		Assert::same(0 * Time::HOUR + 2 * Time::MINUTE + 3 * Time::SECOND, TimeHelper::parse(new DateTime('24:02:03')));

		// arrays
		Assert::same(TRUE, Time::create([])->isNull());
		Assert::same(FALSE, Time::create([])->isZero());
		Assert::same(NULL, TimeHelper::parse([]));
		Assert::same(0, Time::create([])->toSeconds());
		Assert::same(FALSE, Time::create(['foo', 'bar'])->isNull());
		Assert::same(TRUE, Time::create(['foo', 'bar'])->isZero());
		Assert::same(0, TimeHelper::parse(['foo', 'bar']));
		Assert::same(1 * Time::SECOND, TimeHelper::parse([1]));
		Assert::same(1 * Time::HOUR + 2 * Time::MINUTE + 3 * Time::SECOND, TimeHelper::parse([3, 2, 1]));
		Assert::same(1 * Time::WEEK + 2 * Time::DAY + 3 * Time::HOUR + 4 * Time::MINUTE + 5 * Time::SECOND, TimeHelper::parse([5, 4, 3, 2, 1]));
		Assert::same(-1 * Time::WEEK - 2 * Time::DAY - 3 * Time::HOUR - 4 * Time::MINUTE - 5 * Time::SECOND, TimeHelper::parse([-5, - 4, -3, - 2, - 1]));
		Assert::same(1 * Time::WEEK - 2 * Time::DAY + 3 * Time::HOUR - 4 * Time::MINUTE - 5 * Time::SECOND, TimeHelper::parse([-5, - 4, 3, - 2, 1, 'foo', 78554]));

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
				'format' => TimeHelper::FORMAT_HMS,
				'expected' => 12 * Time::HOUR + 30 * Time::MINUTE + 45 * Time::SECOND,
			],
			[
				'input' => '-00:00:01',
				'format' => TimeHelper::FORMAT_HMS,
				'expected' => -1 * Time::SECOND,
			],
			[
				'input' => NULL,
				'format' => TimeHelper::FORMAT_HMS,
				'expected' => NULL,
			],
		];

		// constructor
		foreach ($testValues as $v) {
			Assert::same($v['expected'], (new Time($v['input']))->getRaw());
		}

		// universal factory
		foreach ($testValues as $v) {
			Assert::same($v['expected'], Time::create($v['input'])->getRaw());
		}

		// ->set()
		foreach ($testValues as $v) {
			$t = new TimeMutable();
			Assert::same($v['expected'], $t->set($v['input'])->getRaw());
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


	//--------------------------------------------------------------------------
	//----------------------- Aux methods --------------------------------------



	private function factoryTest($factoryMethodSuffix, $coefficient)
	{
		Assert::same(14 * $coefficient, Time::{'from' . $factoryMethodSuffix}(14)->getRaw());
		Assert::same(13 * $coefficient, Time::{'from' . $factoryMethodSuffix}(13)->getRaw());
		Assert::same(-1 * $coefficient, Time::{'from' . $factoryMethodSuffix}(-1)->getRaw());
		Assert::same(0 * $coefficient, Time::{'from' . $factoryMethodSuffix}(0)->getRaw());
	}

}

// run the test
(new TimeHelperTest)->run();


