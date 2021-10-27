<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Entity\Product;
use DateTimeImmutable;

class ProductTest extends TestCase
{
    const NAME         = "Google Pixel 6";
    const OS           = "Android 11.0";
    const MANUFACTURER = "Google";
    const STORAGE      = 128;
    const PRICE        = 64900;
    const STOCK        = 9;

    public function test_setters(): void
    {
        $CREATED_AT   = new DateTimeImmutable();
        $LAST_UPDATE  = new DateTimeImmutable();

        $product = new Product();

        $product->setName(self::NAME);
        $product->setOs(self::OS);
        $product->setManufacturer(self::MANUFACTURER);
        $product->setStorage(self::STORAGE);
        $product->setPrice(self::PRICE);
        $product->setStock(self::STOCK);

        $product->setCreatedAt($CREATED_AT);
        $product->setLastUpdate($LAST_UPDATE);

        $this->assertEquals(
            $product->getName(),
            self::NAME
        );
        $this->assertEquals(
            $product->getOs(),
            self::OS
        );
        $this->assertEquals(
            $product->getManufacturer(),
            self::MANUFACTURER
        );
        $this->assertEquals(
            $product->getStorage(),
            self::STORAGE
        );
        $this->assertEquals(
            $product->getPrice(),
            self::PRICE
        );
        $this->assertEquals(
            $product->getStock(),
            self::STOCK
        );

        $this->assertEquals(
            $product->getCreatedAt(),
            $CREATED_AT
        );
        $this->assertEquals(
            $product->getLastUpdate(),
            $LAST_UPDATE
        );
    }
}
