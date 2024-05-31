<?php

namespace Cordyceps\Result;

class Err
{
  private $value;

  public function __construct($value)
  {
    $this->value = $value;
  }

  public function getValue()
  {
    return $this->value;
  }
}
