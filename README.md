# What is it?

Cordyceps is a small package that provides two primary classes: `Option` and `Result` for dealing with null values and errors respectively.

## Option

`Option` is a wrapper around values that could be null, which allows us to reduce the number of null checks that we need to do after every operation that might return a null value. After all of our operations are done, only then do we have to unwrap the inner value and check if it actually exists.

Example:
```php
<?php
// this:
function myFunc($arg) {
    $value1 = somethingThatCouldBeNull1($arg);
    if (is_null($value1)) {
        return null;
    }

    $value2 = somethingThatCouldBeNull2($value1);
    if (is_null($value2)) {
        return null;
    }


    $value3 = somethingThatCouldBeNull3($value2);
    if (is_null($value3)) {
        return null;
    }

    // use $value3
}

// ------------
// becomes this:
function myFunc2($arg) {
    $option = Option::make(somethingThatCouldBeNull1($arg))
        // Any callable works here, you can pass regular functions, anon functions and class methods
        // the same way you'd pass them to `call_user_func_array`.
        ->map('somethingThatCouldBeNull2')
        ->map('somethingThatCouldBeNull3')
        ->map(function ($value) {
            // use final $value
        });

    // At the end you *will* need to unwrap the value to see if the chain succeeded.
    // You can query if the wrapped value is "something" or "nothing" with $option->isSome() and $option->isNone().
    // $option->unwrap() will return the wrapped value, be it null or not.
    // If you want to provide a fallback value, you can use $option->unwrapOr($fallback).
    // You can also lazily evaluate the fallback value by using $option->unwrapOrElse(fn () => 'fallback value').
}
```

## Result

`Result` is a wrapper that makes it easier to treat errors as values. This is an alternative to try/catch, the main benefit to treating errors as values is that at the end you are reminded that the operation could fail when it comes the time to actually unwrap the value that could be your "green path" value or an error. Whereas it's very easy to forget to do a try/catch.

Example:
```php
<?php

// this:
function thisCouldGoWrong1($arg) {
    // ...
    if (somethingIsWrong($arg)) {
        throw new Exception('oops');
    }

    return 'some value';
}

function thisCouldGoWrong2($arg) {
    // ...
    if (somethingIsWrong2($arg)) {
        throw new Exception('oops 2');
    }

    return 'some value 2';
}

try {
    $res = thisCouldGoWrong1(42);
    $res = thisCouldGoWrong2($res);
} catch (\Throwable $th) {
    // handle error
}

// ------------
// becomes this:
function thisCouldGoWrong1() {
    // ...
    if (somethingIsWrong()) {
        return Result::makeErr(new Exception('oops'));
    }

    return Result::makeOk('some value');
}

function thisCouldGoWrong2() {
    // ...
    if (somethingIsWrong()) {
        return Result::makeErr(new Exception('oops 2'));
    }

    return Result::makeOk('some value 2');
}

$res = thisCouldGoWrong1(42)
    // just like Option, you can pass anything that would fit in `call_user_func_array`
    ->andThen('thisCouldGoWrong2');

if ($res->isErr()) {
    $exception = $res->unwrap();
    // handle the exception
} else {
    $value = $res->unwrap();
}

// or if you want, you can provide a fallback value, like you can with option:
$value = $res->unwrapOr('fallback');
// or lazily-evaluated version:
$value = $res->unwrapOrElse(fn () => 'fallback');
```

This is all well and good for our code, but what do we do when we have to call old code or 3rd party code that throws exceptions. You can wrap a callable that could throw an exception with `Result::try()`.

Example:
```php
<?php

// Result::try's arguments are the same as call_user_func_array
$res = Result::try('thisCouldThrow', ['func arg', 'func arg 2']);
if ($res->isErr()) {
    // handle error
}
```
