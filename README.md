# What is it?

Cordyceps is a small package that provides two primary classes: `Option` and `Result`.

`Option` is a wrapper around values that could be null, which allows us to reduce the number of null checks that we need to do after every operation that might return a null value. After all of our operations are done, only then do we have to unwrap the inner value and check if it actually exists.

Example:
```php

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

`Result` is a wrapper that makes it easier to treat errors as values.

# TODO: converting our code from throwing exceptions to returning results
# TODO: wrapping third party code that could throw exceptions with results
