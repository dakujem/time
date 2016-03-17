<?php

/**
 * @author Andrej Rypak <xrypak@gmail.com>
 */


namespace Dakujem\Test\Time;

require_once __DIR__ . '/bootstrap.php';

use Dakujem\Time,
	Dakujem\TimeFactory,
	Oliva\Test\DataWrapper,
	Oliva\Utils\Tree\Node\Node,
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

		Assert::same($seconds, (new Time($seconds))->getSeconds());
		Assert::same($seconds, Time::fromSeconds($seconds)->getSeconds());

		Assert::same($seconds, (new TimeFactory)->create($seconds)->getSeconds());
	}


	public function testGetters()
	{
		$timeZero = new Time(0);
		Assert::same(0, $timeZero->getSeconds());
		Assert::same(0, $timeZero->getMinutes());
		Assert::same(0, $timeZero->getHours());
		Assert::same(FALSE, $timeZero->isNegative());

		Assert::same(0, $timeZero->toSeconds());
		Assert::same(0, $timeZero->toMinutes());
		Assert::same(0, $timeZero->toHours());


		$seconds = 6873 * 3600 + 54 * 60 + 18; // 6873 hours 54 minutes 18 seconds

		$time = new Time($seconds);
		Assert::same(18, $time->getSeconds());
		Assert::same(54, $time->getMinutes());
		Assert::same(6873, $time->getHours());
		Assert::same(FALSE, $time->isNegative());

		Assert::same($seconds, $time->toSeconds());
		Assert::same($seconds / 60, $time->toMinutes());
		Assert::same($seconds / 60 / 60, $time->toHours());


		$timeNegative = new Time(-1 * $seconds);
		Assert::same(18, $timeNegative->getSeconds());
		Assert::same(54, $timeNegative->getMinutes());
		Assert::same(6873, $timeNegative->getHours());
		Assert::same(TRUE, $timeNegative->isNegative());

		Assert::same(-1 * $seconds, $timeNegative->toSeconds());
		Assert::same(-1 * $seconds / 60, $timeNegative->toMinutes());
		Assert::same(-1 * $seconds / 60 / 60, $timeNegative->toHours());
	}


	public function testTimeFunctions()
	{
		Assert::same(TRUE, (new Time(0))->isValidDayTime());
		Assert::same(FALSE, (new Time(-1))->isValidDayTime());
		Assert::same(TRUE, (new Time(-1))->clipToDayTime()->isValidDayTime());
		Assert::same(Time::DAY - 1, (new Time(-1))->clipToDayTime()->toSeconds());
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
		Assert::same('00:00:00', (string) new Time(0));
		Assert::same('00:01:00', (string) new Time(60));
		Assert::same('00:01:40', (string) new Time(100));
		Assert::same('00:01:41', (string) new Time(101));
		Assert::same('01:00:00', (string) new Time(3600));
		Assert::same('01:01:40', (string) new Time(3700));
		Assert::same('01:01:41', (string) new Time(3701));

		// not valid day time
		Assert::same('-00:00:01', (string) new Time(-1));
		Assert::same('24:00:00', (string) new Time(Time::DAY));

		// format with persistent signum
		$plusFormat = Time::FORMAT_HMS_SIGNED;
		Assert::same('+00:00:00', (string) (new Time(0))->setFormat($plusFormat));
		Assert::same('+00:00:01', (string) (new Time(1))->setFormat($plusFormat));
		Assert::same('-00:00:01', (string) (new Time(-1))->setFormat($plusFormat));

		// AM / PM
		Assert::same('12:00:00 AM', (string) (new Time(0))->setFormat(Time::FORMAT_HMSA)); // midnight
		Assert::same('12:00:00 PM', (string) (new Time(12 * 60 * 60))->setFormat(Time::FORMAT_HMSA)); // noon / midday
		Assert::same('11:59:59 AM', (string) (new Time(12 * 60 * 60 - 1))->setFormat(Time::FORMAT_HMSA));
		Assert::same('11:59:59 PM', (string) (new Time(24 * 60 * 60 - 1))->setFormat(Time::FORMAT_HMSA));
		Assert::same('12:00:01 PM', (string) (new Time(12 * 60 * 60 + 1))->setFormat(Time::FORMAT_HMSA));
		Assert::same('12:00:01 AM', (string) (new Time(1))->setFormat(Time::FORMAT_HMSA));
		//NOTE: there are no tests for invalid day time with 12h format as the behaviour is undefined
	}


	public function __testScalarOperations()
	{
		// operations are not supported - conversion of Node to scalar types is not possible
		$i = new Node(1);
		$s = new Node('string');

		Assert::error(function()use($i) {
			(int) $i;
		}, E_NOTICE);
		Assert::error(function()use($s) {
			(string) $s;
		}, E_RECOVERABLE_ERROR);
	}


	public function __testArray()
	{
		$array = [1, 2, 3];
		$node = new Node($array);
		Assert::same($array, $node->getObject());

		// cannot call a function on an array
		Assert::exception(function() use ($node) {
			$node->foo();
		}, 'BadMethodCallException');

		// requesting $array['foo'] should raise E_NOTICE
		Assert::error(function() use ($node) {
			$node->foo;
		}, E_NOTICE);

		foreach (array_keys($array) as $key) {
			Assert::same($array[$key], $node->{$key});
		}

		$clone = $array;
		$node[] = 5;
		$clone[] = 5;
		Assert::same($clone, $node->getObject());

		$node[100] = 'foo';
		$clone[100] = 'foo';
		Assert::same($clone, $node->getObject());

		$node[''] = 6;
		$clone[''] = 6;
		Assert::same($clone, $node->getObject());

		// this case is a known bug!
		// cannot be solved without breaking the ability to add by calling $node[] = foo; (PHP 5.6)
		$node[NULL] = 7;
		$clone[NULL] = 7;
		$buggy = [1, 2, 3, 5, 100 => 'foo', '' => 6, 7];
		Assert::notSame($clone, $node->getObject()); // Assert::same will fail - see below
		Assert::same($buggy, $node->getObject()); // $clone !== $buggy
	}


	public function __testObject()
	{
		$dataObject = new DataWrapper(10, 'foobar');
		$node = new Node($dataObject);
		Assert::same($dataObject, $node->getObject());

		Assert::same('foobar', $node->title);
		Assert::same('bar', $node->foo);

		$node->scalar = 4;
		$node->array = $array = [1, 2, 3, [10, 20, 30]];
		$node->object = $object = new DataWrapper('fooId');

		Assert::same(4, $node->scalar);
		Assert::same($array, $node->array);
		Assert::same($object, $node->object);

		// modifying an index of $node->array should raise E_NOTICE - indirect modification
		Assert::error(function() use ($node) {
			$node->array[] = 'foo';
		}, E_NOTICE, 'Indirect modification of overloaded property Oliva\Utils\Tree\Node\Node::$array has no effect');
		Assert::error(function() use ($node) {
			$node->array['foo'] = 'bar';
		}, E_NOTICE, 'Indirect modification of overloaded property Oliva\Utils\Tree\Node\Node::$array has no effect');

		// it works on objects though...
		$object->scalar = 'success';
		Assert::same('success', $node->object->scalar);

		// test function calls
		Assert::same($dataObject, $node->setAttribute('h1', '20px')); // oh, well... one would probably expect setAttribute() to return $node, but...
		Assert::same($node->getAttribute('h1'), '20px');

		// __call test
		Assert::same('Calling "foobar" on an instance of "Oliva\Test\DataWrapper" with 0 arguments.', $node->foobar());
		Assert::same('Calling "foobar2" on an instance of "Oliva\Test\DataWrapper" with 3 arguments.', $node->foobar2(1, 2, 6));
	}


	public function __testClonning()
	{
		$root = new Node('0');
		$root->addChild(new Node(new DataWrapper('foo')));
		$root->addChild(new Node('bar'));
		$clone = clone $root;
		Assert::equal(FALSE, $clone === $root);
		Assert::equal(FALSE, $clone->getChild(0) === $root->getChild(0));
		Assert::equal(TRUE, $clone->getChild(0)->getContents() === $root->getChild(0)->getContents()); // the data not clonned => identical
		Assert::equal(TRUE, $clone->getChild(1)->getContents() === $root->getChild(1)->getContents()); // scalar data is identical
		$clone->getChild(0)->cloneContents(); // clone the data
		Assert::equal(FALSE, $clone->getChild(0)->getContents() === $root->getChild(0)->getContents()); // the data has been clonned
		Assert::equal(TRUE, $clone->getChild(1)->getContents() === $root->getChild(1)->getContents()); // scalar data remains identical
	}

}

// run the test
(new TimeTest)->run();


