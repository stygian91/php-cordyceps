<?php

namespace Cordyceps\Option;

/**
 * @template T
 */
class Some
{
  /** @var T */
  private $value;

  /** @param T $value */
  public function __construct($value)
  {
    $this->value = $value;
  }

  /** @return T */
  public function getValue()
  {
    return $this->value;
  }
}
