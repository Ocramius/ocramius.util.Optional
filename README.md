# `ocramius\util\Optional`

This package is a PHP port of the `java.util.Optional` class in the 
[OpenJDK](http://hg.openjdk.java.net/lambda/lambda/jdk/file/tip/src/share/classes/java/util/Optional.java) libraries.

You can find the API of `java.lang.Optional` in the 
[Java 8 API docs](http://docs.oracle.com/javase/8/docs/api/java/util/Optional.html).

# differences

 -> empty() -> newEmpty()
 type-safety is not ensured: may change the API in 2.0 to allow passing in types for runtime type-safety checks
 toString -> __toString
