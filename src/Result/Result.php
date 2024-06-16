<?php

namespace Cordyceps\Result;

use Cordyceps\Option\Option;
use Exception;
use Throwable;

/**
 * @template T
 */
class Result
{
  /** @var Err|Ok<T> */
  private $wrapped;

  /**
   * @param Err|Ok<T> $value
   */
  public function __construct($value)
  {
    $this->wrapped = $value;
  }

  public static function try(callable $fn, array $args = []): self
  {
    try {
      $fnRes = call_user_func_array($fn, $args);
      $res = new static(new Ok($fnRes));
    } catch (\Throwable $th) {
      $res = new static(new Err($th));
    }

    return $res;
  }

  public static function makeErr(Throwable $throwable): self
  {
    return new static(new Err($throwable));
  }

  public static function makeOk($value = null): self
  {
    return new static(new Ok($value));
  }

  public static function makeErrException($message = "", $code = 0, $previous = null)
  {
    return new static(new Err(new Exception($message, $code, $previous)));
  }

  /**
   * @param callable(T): mixed
   */
  public function map(callable $fn): self
  {
    if ($this->isOk()) {
      return static::makeOk(call_user_func_array($fn, [$this->unwrap()]));
    }

    return $this;
  }

  public function and(Result $other): self
  {
    if ($this->isOk()) {
      return $other;
    }

    return $this;
  }

  /**
   * @param callable(T): self $fn
   */
  public function andThen(callable $fn): self
  {
    if ($this->isOk()) {
      return call_user_func_array($fn, [$this->unwrap()]);
    }

    return $this;
  }

  /**
   * @param callable(Throwable): Throwable $fn
   */
  public function mapErr(callable $fn): self
  {
    if ($this->isErr()) {
      return static::makeErr(call_user_func_array($fn, [$this->unwrap()]));
    }

    return $this;
  }

  public function or(self $other): self
  {
    return $this->isOk() ? $this : $other;
  }

  /** @param callable(): self $fn */
  public function orElse(callable $fn): self
  {
    return $this->isOk() ? $this : call_user_func_array($fn, []);
  }

  public function isErr(): bool
  {
    return is_a($this->wrapped, Err::class);
  }

  public function isOk(): bool
  {
    return is_a($this->wrapped, Ok::class);
  }

  /**
   * @return \Throwable|mixed
   */
  public function unwrap()
  {
    return $this->wrapped->getValue();
  }

  /**
   * @param mixed $default
   * @return \Throwable|mixed
   */
  public function unwrapOr($default)
  {
    return $this->isOk() ? $this->unwrap() : $default;
  }

  /**
   * @return mixed
   */
  public function unwrapOrElse(callable $fnDefault)
  {
    return $this->isOk() ? $this->unwrap() : call_user_func_array($fnDefault, []);
  }

  public function toOption()
  {
    return $this->isOk() ? Option::make($this->unwrap()) : Option::makeNone();
  }
}
