# Time

A simple to use powerful class that makes working with time and temporal data easy.

On the contrary to DateTime or Carbon (nesbot/carbon), `Time` is simply a container for time measured in seconds,
it does not refer to any real point-in-time, there are no time zones no nothing.

The aim of the Time library is to help working with temporal data
like work timer measurements, schedules, time-tables, subscription timers etc.

Your data may be stored in seconds, minutes, hours or in formatted strings, working with `Time` will still be the same.

For example, `Time` supports working with **negative times** and **times exceeding 24 hours**, as opposed to `DateTime` or `Carbon`:
```php
// negative time values
(string) Time::create('-10:30'); // -10:30:00
(string) Time::create('10:30')->subHours(100); // -89:30:00

// time values exceeding 24 hours
Time::create('50:00:00'); // 50 hours
Time::fromDays(10); // 10 days
```

Furthermore, with `Time` you can **compare** time values, perform **atirhmetic operations** or **convert** them.
You can **parse strings** containing formatted time values and you can print the `Time` object according to a specified **format**.

## Methods

There are many methods for **handling**, **converting**, **modifying**, **reading** and **printing** the time value:
```php
// arithmetic methods: add, sub, mult, div, mod
(new Time('12:00'))->add(Time::fromSeconds(17000))->sub(new Time(45))
Time::fromHours(10)->div(2) // 5 hours

// comparison methods: lt, lte, gt, gte, eq, neq, between
(new Time('12:00'))->between('12:30', '11:30') // TRUE

// adding values: addSeconds, addMinutes, addHours, addDays, addWeeks
// substracting values: subSeconds, subMinutes, subHours, subDays, subWeeks
(string) Time::create(123) //  00:02:03
        ->addSeconds(10)   //  00:02:13
        ->addSeconds(-10)  //  00:02:03
        ->subMinutes(-10)  // -00:08:03

// conversion options: toSeconds, toMinutes, toHours, toDays, toWeeks
Time::fromWeeks(4)->toSeconds() // 2419200
Time::fromDays(3.5)->toWeeks()  // 0.5

// options to create the time object: constructor, create, fromSeconds, fromMinutes, fromHours, fromDays, fromWeeks
new Time('12:20') == Time::create(12:20)
Time::fromDays(4)

// string time parsing (reading)
Time::create('10:30 PM')
Time::create('23:59:59')
Time::create('-10:30')

// output formatting
(string) Time::create(123)->format(Time::FORMAT_HM); // 00:02 - custom format (HH:mm)
(string) Time::fromHours(2, 3, 4); // 02:03:04 - the default format (HH:mm:ss)

// converting to DateTime or Carbon: toDateTime, toCarbon
$carbon = (string) Time::create(123)->toCarbon();
$dt = (string) Time::create('07:50 AM')->toDateTime();

// clipping to valid day time
(string) Time::create(-1); // -00:00:01
(string) Time::create(-1)->clipToDayTime(); // 23:59:59
(Time::create('23:59:59'))          // 23:59:59
        ->addSeconds(1)             // 24:00:00
        ->clipToDayTime();          // 00:00:00
```
For all the methods, please refer to the source code.

## Mutable and Immutable Time objects

The default `Time` object is **mutable**.
```php
$time = Time::fromSeconds(0);
(string) $time->addSeconds(30)->mult(2); // "00:01:00"
$time->getMinutes(); // 1  -- the modifications are accumulated inside the Time instance
```
Sometimes one needs to treat a time object as an **immutable object**, the solution is the `TimeImmutable` class.
```php
$immutable = TimeImmutable::fromSeconds(0);
(string) $immutable->addSeconds(30)->mult(2); // "00:01:00"  -- all the operations work as expected
$immutable->getMinutes(); // 0  -- but the instance itself does not change - this is in contrast to the default Time object
```
Once a `TimeImmutable` instance is initialized, its value does not change. Upon any modification a new instance is returned.


## Installation
The easiest way to install Time is to use [Composer](https://getcomposer.org/). Just add `"dakujem/time"` to the "require" section in your `composer.json` file, like this:
```json
{
	"require": {
		"dakujem/time": '*'
	}
}
```

Note: `dakujem/time` is built for and tested to run on PHP 5.6 and PHP 7 and above, however, it should also run on PHP 5.4 and 5.5 if needed.


----

> **Warning**: This library is provided **as-is** with absolutely **no warranty** nor any liability from its contributors for anything it's usage, manipulation or distribution may cause.
