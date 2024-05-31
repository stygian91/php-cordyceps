<?php

namespace Cordyceps\Result;

/**
 * @template T
 */
class Ok
{
  /** @var T */
  private $value;

  /**
   * @param T $value
   */
  public function __construct($value)
  {
    $this->value = $value;
  }

  /**
   * @return T
   */
  public function getValue()
  {
    return $this->value;
  }
}
