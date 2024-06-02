<?php

declare(strict_types=1);

use Cordyceps\Result\Result;
use PHPUnit\Framework\TestCase;

class ResultException extends Exception
{
}

final class ResultTest extends TestCase
{
  public function testMap()
  {
    $okRes = Result::makeOk()->map(fn () => 42);
    $errRes = static::makeErr()->map(fn () => 42);

    $this->assertTrue($okRes->isOk());
    $this->assertFalse($okRes->isErr());
    $this->assertEquals(42, $okRes->unwrap());

    $this->assertTrue($errRes->isErr());
    $this->assertFalse($errRes->isOk());
    $this->assertEquals(Exception::class, get_class($errRes->unwrap()));
  }

  public function testOr()
  {
    $okRes = Result::makeOk(1);
    $okRes2 = Result::makeOk(2);
    $errRes = static::makeErr();

    $this->assertEquals(1, $okRes->or($okRes2)->unwrap());
    $this->assertEquals(2, $errRes->or($okRes2)->unwrap());

    $outside = 0;
    $orElse = function () use (&$outside) {
      $outside++;
      return Result::makeOk(3);
    };

    $this->assertEquals(1, $okRes->orElse($orElse)->unwrap());
    $this->assertEquals(0, $outside);
    $this->assertEquals(3, $errRes->orElse($orElse)->unwrap());
    $this->assertEquals(1, $outside);
  }

  public function testAnd()
  {
    $okRes = Result::makeOk(1);
    $okRes2 = Result::makeOk(2);
    $errRes = static::makeErr();

    $this->assertEquals(2, $okRes->and($okRes2)->unwrap());
    $this->assertTrue($errRes->and($okRes)->isErr());

    $okFun = function ($x) {
      return Result::makeOk($x + 10);
    };
    $this->assertTrue($okRes->andThen($okFun)->isOk());
    $this->assertEquals(11, $okRes->andThen($okFun)->unwrap());

    $this->assertTrue($errRes->andThen($okFun)->isErr());
  }

  public function testMapErr()
  {
    $errRes = static::makeErr();
    $wrappedErr = $errRes->mapErr(fn ($exc) => new ResultException($exc->getMessage()));

    $this->assertEquals(ResultException::class, get_class($wrappedErr->unwrap()));
    $this->assertEquals('test exception', $wrappedErr->unwrap()->getMessage());
  }

  public function testTry()
  {
    $okRes = Result::try(fn ($x) => $x, [42]);
    $errRes = Result::try(function () {
      throw new Exception('test exception');
    });

    $this->assertEquals(42, $okRes->unwrap());
    $this->assertTrue($errRes->isErr());
    $this->assertEquals('test exception', $errRes->unwrap()->getMessage());
  }

  public function testUnwrapOr()
  {
    $okRes = Result::makeOk(1);
    $errRes = static::makeErr();

    $this->assertEquals(1, $okRes->unwrapOr(2));
    $this->assertEquals(2, $errRes->unwrapOr(2));

    $outside = 0;
    $orElse = function () use (&$outside) {
      $outside++;
      return 2;
    };

    $this->assertEquals(1, $okRes->unwrapOrElse($orElse));
    $this->assertEquals(0, $outside);
    $this->assertEquals(2, $errRes->unwrapOrElse($orElse));
    $this->assertEquals(1, $outside);
  }

  private static function makeErr()
  {
    return Result::makeErrException('test exception');
  }
}
