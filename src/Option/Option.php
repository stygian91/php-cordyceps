<?php

namespace Cordyceps\Option;

use Cordyceps\Result\Result;
use Throwable;

/**
 * @template T
 */
class Option
{
  /** @var Some<T>|None */
  private $wrapped;

  /**
   * @param Some<T>|None $value
   */
  public function __construct($value)
  {
    $this->wrapped = $value;
  }

  public static function make($value): self
  {
    return is_null($value) ? static::makeNone() : static::makeSome($value);
  }

  public static function makeSome($value): self
  {
    return new static(new Some($value));
  }

  public static function makeNone(): self
  {
    return new static(new None);
  }

  /**
   * @param Option[] $options
   * @returns Option<array>
   */
  public static function flipList($options)
  {
    $out = [];

    foreach ($options as $option) {
      if ($option->isNone()) {
        return $option;
      }

      $out[] = $option->unwrap();
    }

    return Option::make($out);
  }

  /**
   * @param callable(T): mixed $fn
   */
  public function map(callable $fn): self
  {
    if ($this->isSome()) {
      $newValue = call_user_func_array($fn, [$this->unwrap()]);
      return static::make($newValue);
    }

    return $this;
  }

  public function or(self $other): self
  {
    return $this->isSome() ? $this : $other;
  }

  /**
   * @param callable(): self $fn
   */
  public function orElse(callable $fn): self
  {
    return $this->isSome() ? $this : call_user_func_array($fn, []);
  }

  public function and(Option $other)
  {
    if ($this->isSome()) {
      return $other;
    }

    return $this;
  }

  /**
   * @param callable(mixed): self $fn
   */
  public function andThen(callable $fn): self
  {
    if ($this->isSome()) {
      return call_user_func_array($fn, [$this->unwrap()]);
    }

    return $this;
  }

  public function isNone(): bool
  {
    return is_a($this->wrapped, None::class);
  }

  public function isSome(): bool
  {
    return !$this->isNone();
  }

  public function getWrapped()
  {
    return $this->wrapped;
  }

  /**
   * @return T|null
   */
  public function unwrap()
  {
    return $this->wrapped->getValue();
  }

  public function unwrapOr($default)
  {
    return $this->isSome() ? $this->wrapped->getValue() : $default;
  }

  public function unwrapOrElse(callable $fnDefault)
  {
    return $this->isSome() ? $this->wrapped->getValue() : call_user_func_array($fnDefault, []);
  }

  /**
   * @param Throwable|null $previous
   * @return Result
   */
  public function toResult(string $message = '', int $code = 0, $previous = null)
  {
    return $this->isSome() ? Result::makeOk($this->unwrap()) : Result::makeErr(new MissingException($message, $code, $previous));
  }
}
