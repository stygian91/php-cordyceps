<?php

namespace Cordyceps\Result;

use Throwable;

class Result
{
  private $wrapped;

  public function __construct($value)
  {
    $this->wrapped = is_a($value, Throwable::class) ? new Err($value) : new Ok($value);
  }

  public static function try(callable $fn, array $args): static
  {
    try {
      $fnRes = call_user_func_array($fn, $args);
      $res = new static($fnRes);
    } catch (\Throwable $th) {
      $res = new static($th);
    }

    return $res;
  }

  public function map(callable $fn): static
  {
    if ($this->isOk()) {
      return new static(call_user_func_array($fn, [$this->unwrap()]));
    }

    return $this;
  }

  public function andThen(callable $fn): static
  {
    if ($this->isOk()) {
      return call_user_func_array($fn, [$this->unwrap()]);
    }

    return $this;
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
}
