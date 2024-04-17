<?php

namespace Cordyceps\Traits;

trait Value
{
  public function __construct(private $value)
  {
  }

  public function getValue()
  {
    return $this->value;
  }
}
