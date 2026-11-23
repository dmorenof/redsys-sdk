<?php
declare(strict_types=1);

namespace Redsys\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redsys\Dto\Result;
use Redsys\ResponseCodes;

final class ResultTest extends TestCase
{
    /** @return iterable<string, array{string, bool}> */
    public static function responses(): iterable
    {
        foreach (['0000' => true, '0001' => true, '0099' => true, '0100' => false, '101' => false, '900' => true, '400' => true, '9915' => false, '9999' => false, '' => false, 'abc' => false] as $code => $authorised) {
            yield "Ds_Response '$code'" => [(string) $code, $authorised];
        }
    }

    #[DataProvider('responses')]
    public function testOnlyTheCodesOfTheManualAreAuthorised(string $code, bool $authorised): void
    {
        $result = new Result();
        $result->response = $code;
        self::assertSame($authorised, $result->authorised());
    }

    public function testAResultWithoutAResponseIsNotAuthorised(): void
    {
        self::assertFalse((new Result())->authorised());
    }

    public function testTheCodesShopsMeetMostAreDescribed(): void
    {
        self::assertSame('Authorised', ResponseCodes::describe('0000'));
        self::assertSame('Authorised', ResponseCodes::describe('0042'));
        self::assertSame('Card expired', ResponseCodes::describe('101'));
        self::assertSame('Repeated order number', ResponseCodes::describe('913'));
        self::assertNull(ResponseCodes::describe('7777'));
        self::assertNull(ResponseCodes::describe('x'));
    }
}
