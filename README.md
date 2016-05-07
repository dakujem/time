# Time

A simple to use powerful class that makes working with time and temporal data easy.

On the contrary to DateTime or Carbon (nesbot/carbon), Time is simply a container for time measured in seconds,
it does not refer to any real point-in-time, there are no time zones no nothing.

The aim of the Time library is to help working with temporal data
like work timer measurements, schedules, time-tables, subscription timers etc.

Your data may be stored in seconds, minutes, hours or in formatted strings, working with Time will still be the same.

```php
// you can not do this with DateTime nor Carbon:
(string) Time::create('10:30')->subHours(100); // -89:30:00
(string) Time::create('-10:30'); // -10:30:00

(string) Time::create(-1); // -00:00:01
(string) Time::create(-1)->clipToDayTime(); // 23:59:59

(string) Time::create(123)->format(Time::FORMAT_HM); // 00:02
(string) Time::fromHours(2, 3, 4); // 02:03:04

```


## Installation
The easiest way to install Time is to use [Composer](https://getcomposer.org/). Just add `"dakujem/time"` to the "require" section in your `composer.json` file, like this:
```json
{
	"require": {
		"dakujem/time": '*'
	}
}
```

Note: `dakujem/time` is built for and tested to run on PHP 5.6 and PHP 7 and above, however, it should also run on PHP 5.4 and 5.5.


----

> **Warning**: This library is provided **as-is** with absolutely **no warranty** nor any liability from its contributors for anything it's usage, manipulation or distribution may cause.
