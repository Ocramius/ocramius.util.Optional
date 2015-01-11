# `ocramius\util\Optional`

[![Build Status](https://travis-ci.org/Ocramius/ocramius.util.Optional.svg?branch=master)](https://travis-ci.org/Ocramius/ocramius.util.Optional)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Ocramius/ocramius.util.Optional/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Ocramius/ocramius.util.Optional/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/Ocramius/ocramius.util.Optional/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Ocramius/ocramius.util.Optional/?branch=master)

This package is a PHP port of the `java.util.Optional` class in the 
[OpenJDK](http://hg.openjdk.java.net/lambda/lambda/jdk/file/tip/src/share/classes/java/util/Optional.java) libraries.

You can find the API of `java.lang.Optional` in the 
[Java 8 API docs](http://docs.oracle.com/javase/8/docs/api/java/util/Optional.html).

## Installation

```sh
composer require ocramius/optional:dev-master
```

This package is still not stable, but there shouldn't be any breaking changes before `1.0.0`.

## Differences with the Java implementation

Because of PHP's current limitations, I had to rewrite some bits of the Java implementation as follows:

 * `Optional#empty()` is named `Optional#newEmpty()`, because `empty` is a reserved PHP keyword
 * type-safety is not ensured at any time: generics have simply been stripped from the `Optional` implementation.
   This may change in future, but I don't plan to do it right now.
 * `Optional#toString()` is named `Optional#__toString()` in accordance to 
   [PHP magic methods naming](http://php.net/manual/en/language.oop5.magic.php#object.tostring)
 * `Consumer`, `Predicate`, `Function` and `Supplier` arguments are simply `callable`, for simplicity and flexibility.

## License

Since this library is a direct port of the OpenJDK sources, I have to keep the original license in place, which is
GPLv2 + ClassPath exceptions.
