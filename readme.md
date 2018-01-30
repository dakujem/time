# Time

A no-nonsense library for working with time and doing temporal calculations the easy way.

The aim of the **Time toolkit** is to help working with temporal data
like work timer measurements, schedules, time-tables, subscription timers etc.

For example, `Time` supports working with **negative times** and **times exceeding 24 hours**, as opposed to `DateTime` or `Carbon`:
```php
// negative time values
(string) Time::create('-10:30'); // -10:30:00
(string) Time::create('10:30')->subHours(100); // -89:30:00

// time values exceeding 24 hours
Time::create('50:00:00'); // 50 hours
Time::fromDays(10); // 10 days
```

On the contrary to DateTime or Carbon (nesbot/carbon), `Time` is simply a container for time measured in seconds,
it does not refer to any real point-in-time, there are no time zones no nothing.

With `Time` you can **compare** time values, perform **atirhmetic operations** or **convert** them.
You can **parse strings** containing formatted time values and you can print the `Time` object according to a specified **format**.

Your data may be stored in seconds, minutes, hours or in formatted strings, working with `Time` will still be the same.


## Methods

There are many methods for **handling**, **converting**, **modifying**, **reading** and **printing** the time value.

Options to create a time object:
```php
// constructor, create, fromSeconds, fromMinutes, fromHours, fromDays, fromWeeks
new Time('12:20') == Time::create('12:20')
Time::fromDays(4)
Time::fromHours(12, 20) == Time::create('12:20') == Time::create(44400) == Time::fromSeconds(44400)
```

Arithmetic methods:
```php
// arithmetic methods: add, sub, mult, div, mod
(new Time('12:00'))->add(Time::fromSeconds(17000))->sub(new Time(45))
Time::fromHours(10)->div(2) // 5 hours
```

Comparisons:
```php
// comparison methods: lt, lte, gt, gte, eq, neq, between
(new Time('12:00'))->between('12:30', '11:30') // TRUE
```

Adding / subtracting seconds, minutes, hours, days or weeks:
```php
// adding values: addSeconds, addMinutes, addHours, addDays, addWeeks
// subtracting values: subSeconds, subMinutes, subHours, subDays, subWeeks
(string) Time::create(123) //  00:02:03
        ->addSeconds(10)   //  00:02:13
        ->addSeconds(-10)  //  00:02:03
        ->subMinutes(-10)  // -00:08:03
```

Conversion options:
```php
// conversion options: toSeconds, toMinutes, toHours, toDays, toWeeks
Time::fromWeeks(4)->toSeconds() // 2419200
Time::fromDays(3.5)->toWeeks()  // 0.5
```

Input parsing (reading) and output formatting:
```php
// string time parsing (reading)
Time::create('23:59:59')->toSeconds() == TimeHelper::parse('23:59:59')
TimeHelper::parse('-10:30')
Time::create(TimeHelper::parse('10:30 PM', TimeHelper::FORMAT_HMA)) // custom format

// output formatting
(string) Time::create(123)->format(Time::FORMAT_HM); // 00:02 - custom format (HH:mm)
(string) Time::fromHours(2, 3, 4); // 02:03:04 - the default format (HH:mm:ss)
```

Output to other time objects (conversion):
```php
// converting to DateTime or Carbon: toDateTime, toCarbon
$carbon = Time::create(123)->toCarbon();
$datetm = Time::create('07:50 AM')->toDateTime();
```

Validating valid day time and clipping to valid day time:
```php
// clipping to valid day time
Time::create(-1)->isValidDayTime(); // FALSE
(string) Time::create(-1); // -00:00:01
(string) Time::create(-1)->clipToDayTime(); // 23:59:59
(Time::create('23:59:59'))          // 23:59:59
        ->addSeconds(1)             // 24:00:00
        ->clipToDayTime();          // 00:00:00
```

And there is more!

>**Note**: For all the methods, please refer to the **source code**.

## Mutable and Immutable Time objects

The default `Time` object is **immutable**.
It means that once a `Time` instance is created, its value does not change. Upon any modification a new instance is returned.
```php
$immutable = Time::fromSeconds(0);
// all the operations work as expected:
(string) $immutable->addSeconds(30)->mult(2); // "00:01:00"
// but the instance itself does not change - this is in contrast to the mutable TimeMutable object:
$immutable->getMinutes(); // 0
```
Sometimes one needs to treat a time object as a **mutable object**, an accumulator, the solution is the `TimeMutable` class.
```php
$mutable = TimeMutable::fromSeconds(0);
(string) $mutable->addSeconds(30)->mult(2); // "00:01:00"
// the modifications are accumulated inside the TimeMutable instance:
$mutable->getMinutes(); // 1
```
`TimeMutable` may be useful for aggregations:
```php
$acc = new TimeMutable();
foreach(... as $foo){
    $acc->add($foo->getDuration());
}
print $acc;
```

## Parsing time strings and formatting

By default, `Time` accepts time strings in `HH:MM:SS` format:
```php
Time::create('23:59:59')->sub('12:30')
```
The default format is stored in `TimeHelper::$defaultFormat` static variable and can be changed.
However, it is always more flexible to use a factory and pass `Time` instances into calculations:
```php
$timeFactory = new TimeFactory();
$timeFactory->create('23:59:59')->sub($timeFactory->create('12:30'))
```
This way the format of the input can be changed at runtime and can be set for all the factories independently.
The factories can also be implemented in custom way and use custom parsers.

To use **different formats** without using a factory, `TimeHelper` can be used manually:
```php
Time::create(TimeHelper::parse('23:59:59'))->sub(TimeHelper::parse('10:30 PM', TimeHelper::FORMAT_HMA))
```

The same principle is true for formatting. Converting the time objects to string is done using the format stored in `TimeHelper::$defaultFormat`, that is `HH:MM:SS` by default.

## Milliseconds, microseconds...

The `Time` object is built for calculations in **seconds**, which is the maximum integer resolution for which all the features work.

However, it is possible to use `double` time values to get heigher resolution (milliseconds or microseconds):
```php
Time::create(5.500) // 5.5 seconds
```
Features that do not work correctly (yet) when using double:
- parsing - custom parsing needed
- formatting - `TimeHelper` is currently unable to format double correctly
- there is no round or abs implementation yet

>**Note**: When using **double** data type all the PHP implementation limitations apply.

## Installation

The easiest way to install *Time* is to use [Composer](https://getcomposer.org/).

Run `composer require dakujem/time` command.

Alternatively, add `"dakujem/time"` to the "require" section in your `composer.json` file, like this:
```json
	"require": {
		"dakujem/time": "^1"
	}
```

> **Note**: `dakujem/time` is built for and tested to run on PHP 5.6 and PHP 7 and above, however, it should also run on PHP 5.4 and 5.5 if needed.

