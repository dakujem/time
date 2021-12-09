
# Changelog

> 📖 back to [readme](readme.md)

Time follows semantic versioning.\
Any issues should be reported.


## v1.1

Supports PHP 8.1 and rises minimum PHP version to 7.2.

**Edge case**\
Fixes previously incoherent behaviour of methods `Time::mod` and `Time::clipToDayTime` when the internal value is `null`.\
This has been fixed and calling these methods will result in a new `Time` instance with internal `null` value as well (previously it was `0`).


## v1

The initial stable version.
