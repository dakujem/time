<?php

/**
 * @author Andrej Rypak <xrypak@gmail.com>
 */


namespace Dakujem\Test\Time;

require_once __DIR__ . '/bootstrap.php';

use Dakujem\Time,
	Dakujem\TimeFactory,
	Exception,
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


	public function testFactories()
	{
		$seconds = 14;

		Assert::same($seconds, (new Time($seconds))->toSeconds());
		Assert::same($seconds, Time::fromSeconds($seconds)->toSeconds());

		Assert::same($seconds, (new TimeFactory)->create($seconds)->toSeconds());
	}


	public function testGetters()
	{
		$timeZero = Time::fromSeconds(0);
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
		Assert::same(18, $time->getSeconds());
		Assert::same(54, $time->getMinutes());
		Assert::same(6873, $time->getHours());
		Assert::same(FALSE, $time->isNegative());
		Assert::same(1, $time->getSignum());

		Assert::same($seconds, $time->toSeconds());
		Assert::same($seconds / 60, $time->toMinutes());
		Assert::same($seconds / 60 / 60, $time->toHours());


		$timeNegative = Time::fromSeconds(-1 * $seconds);
		Assert::same(18, $timeNegative->getSeconds());
		Assert::same(54, $timeNegative->getMinutes());
		Assert::same(6873, $timeNegative->getHours());
		Assert::same(TRUE, $timeNegative->isNegative());
		Assert::same(-1, $timeNegative->getSignum());

		Assert::same(-1 * $seconds, $timeNegative->toSeconds());
		Assert::same(-1 * $seconds / 60, $timeNegative->toMinutes());
		Assert::same(-1 * $seconds / 60 / 60, $timeNegative->toHours());
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
		// format constants
		Assert::same('?H:i:s', Time::FORMAT_HMS);
		Assert::same('+H:i:s', Time::FORMAT_HMS_SIGNED);
		Assert::same('?H:i', Time::FORMAT_HM);
		Assert::same('+H:i', Time::FORMAT_HM_SIGNED);
		Assert::same('h:i:s A', Time::FORMAT_HMSA);
		Assert::same('h:i A', Time::FORMAT_HMA);

		// default format is '?H:i:s'
		Assert::same(Time::FORMAT_HMS, (new Time)->getFormat());
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
		Assert::same('12:34:56', (string) Time::create('12:34:56'));
		Assert::same(45296, Time::create('12:34:56')->toSeconds());
		Assert::same(300, Time::create('00:05')->toSeconds());
		Assert::same(300, Time::create('0:5')->toSeconds());
		Assert::same(5, Time::create('00:00:05')->toSeconds());
		Assert::same(5, Time::create('0:0:5')->toSeconds());
		Assert::same(45000, Time::create('12:30 PM')->toSeconds());
		Assert::same(1800, Time::create('12:30 AM')->toSeconds());

		//TODO for now invalid day times cannot be parsed
		Assert::error(function() {
			Assert::same(45296, Time::create('123:34')->toSeconds());
		}, Exception::CLASS);
		Assert::error(function() {
			Assert::same(45296, Time::create('-123:34:12')->toSeconds());
		}, Exception::CLASS);
		Assert::error(function() {
			Assert::same(45296, Time::create('-123:34:12')->toSeconds());
		}, Exception::CLASS);

		//TODO test carbon / datetime
	}

}

// run the test
(new TimeTest)->run();


