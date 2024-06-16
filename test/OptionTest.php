<?php

declare(strict_types=1);

use Cordyceps\Option\MissingException;
use Cordyceps\Option\Option;
use PHPUnit\Framework\TestCase;

final class OptionTest extends TestCase
{
  public function testMap()
  {
    $opt = Option::make(42);
    $res = $opt->map(function ($x) {
      return $x + 10;
    });

    $this->assertTrue($res->isSome());
    $this->assertFalse($res->isNone());
    $this->assertEquals(52, $res->unwrap());
  }

  public function testMultipleMaps()
  {
    $opt = Option::make(12);
    $res = $opt->map(function ($x) {
      return $x + 10;
    })->map(function ($x) {
      return $x / 2;
    });

    $this->assertTrue($res->isSome());
    $this->assertFalse($res->isNone());
    $this->assertEquals(11, $res->unwrap());
  }

  public function testMapNull()
  {
    $opt = Option::make(null);
    $outside = 0;
    $res = $opt->map(function ($x) use (&$outside) {
      $outside = 1;
      return $x + 10;
    });

    $this->assertEquals(0, $outside);
    $this->assertTrue($res->isNone());
    $this->assertFalse($res->isSome());
  }

  public function testMapReturningNull()
  {
    $opt = Option::make(420);
    $res = $opt->map(function () {
      return null;
    });

    $this->assertTrue($res->isNone());
    $this->assertFalse($res->isSome());
  }

  public function testAnd()
  {
    $someOpt = Option::makeSome(1);
    $someOpt2 = Option::makeSome(2);
    $noneOpt = Option::makeNone();

    $andRes = $someOpt->and($someOpt2);
    $noneRes = $noneOpt->and($someOpt2);
    $noneRes2 = $someOpt->and($noneOpt);

    $this->assertTrue($andRes->isSome());
    $this->assertEquals(2, $andRes->unwrap());
    $this->assertTrue($noneRes->isNone());
    $this->assertTrue($noneRes2->isNone());
  }

  public function testAndThen()
  {
    $opt = Option::make(69);
    $res = $opt->andThen(function ($x) {
      return Option::make($x + 420);
    });

    $this->assertTrue($res->isSome());
    $this->assertEquals(489, $res->unwrap());
  }

  public function testAndThenNone()
  {
    $opt = Option::make(69);
    $res = $opt->andThen(function () {
      return Option::makeNone();
    });

    $this->assertTrue($res->isNone());
  }

  public function testOr()
  {
    $res1 = Option::make(69)->or(Option::make(42));
    $res2 = Option::makeNone()->or(Option::make(42));

    $this->assertTrue($res1->isSome());
    $this->assertEquals(69, $res1->unwrap());
    $this->assertTrue($res2->isSome());
    $this->assertEquals(42, $res2->unwrap());
  }

  public function testOrElse()
  {
    $res1 = Option::make(420)->orElse(function () {
      return Option::make(69);
    });
    $res2 = Option::makeNone()->orElse(function () {
      return Option::make(69);
    });

    $this->assertTrue($res1->isSome());
    $this->assertEquals(420, $res1->unwrap());
    $this->assertTrue($res2->isSome());
    $this->assertEquals(69, $res2->unwrap());
  }

  public function testUnwrapOr()
  {
    $res1 = Option::make(42)->unwrapOr(69);
    $res2 = Option::makeNone()->unwrapOr(69);

    $this->assertEquals(42, $res1);
    $this->assertEquals(69, $res2);
  }

  public function testUnwrapOrElse()
  {
    $outside = 0;
    $fn = function () use (&$outside) {
      $outside += 1;
      return 69;
    };
    $res1 = Option::make(42)->unwrapOrElse($fn);
    $res2 = Option::makeNone()->unwrapOrElse($fn);

    $this->assertEquals(42, $res1);
    $this->assertEquals(69, $res2);
    $this->assertEquals(1, $outside);
  }

  public function testToResult()
  {
    $opt1 = Option::makeNone();
    $opt2 = Option::make(42);

    $res1 = $opt1->toResult('example', 99);
    $res2 = $opt2->toResult();

    $this->assertTrue($res1->isErr());
    $this->assertEquals(MissingException::class, get_class($res1->unwrap()));
    $this->assertEquals('example', $res1->unwrap()->getMessage());
    $this->assertEquals(99, $res1->unwrap()->getCode());

    $this->assertTrue($res2->isOk());
    $this->assertEquals(42, $res2->unwrap());
  }

  public function testFlipList()
  {
    $opt1 = Option::makeNone();
    $opt2 = Option::make(42);
    $opt3 = Option::make(69);

    $this->assertTrue(Option::flipList([$opt1, $opt2])->isNone());
    $this->assertTrue(Option::flipList([$opt2, $opt3])->isSome());
    $this->assertEquals([42, 69], Option::flipList([$opt2, $opt3])->unwrap());
  }
}
