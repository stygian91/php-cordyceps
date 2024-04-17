<?php

namespace Cordyceps\Option;

class Option
{
  private $wrapped;

  public function __construct($value)
  {
    $this->wrapped = is_null($value) ? new None : new Some($value);
  }

  public function map(callable $fn): static
  {
    if ($this->isSome()) {
      return new static(call_user_func_array($fn, [$this->unwrap()]));
    }

    return $this;
  }

  public function andThen(callable $fn): static
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

  /**
   * @return mixed|null
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
}
