<?php declare(strict_types=1);

namespace Axytos\Shopware\Tests\DataMapping;

use Axytos\Shopware\DataMapping\RefundBasketPositionDtoFactory;
use PHPUnit\Framework\TestCase;

class RefundBasketPositionDtoFactoryTest extends TestCase
{    
    private RefundBasketPositionDtoFactory $sut;

    private string $productId = 'productId';
    private float $grossRefundTotal = 12.2;
    private float $netRefundTotal = 10.4;

    public function setUp(): void
    {
        $this->sut = new RefundBasketPositionDtoFactory();
    }

    public function test_maps_productId() : void
    {
        $actual = $this->sut->create($this->productId, $this->grossRefundTotal, $this->netRefundTotal)->productId;

        $this->assertEquals($this->productId, $actual);
    }

    public function test_maps_calculates_grossRefundTotal() : void
    {
        $actual = $this->sut->create($this->productId, $this->grossRefundTotal, $this->netRefundTotal)->grossRefundTotal;

        $this->assertSame($this->grossRefundTotal, $actual);
    }

    public function test_maps_calculates_netRefundTotal() : void
    {
        $actual = $this->sut->create($this->productId, $this->grossRefundTotal, $this->netRefundTotal)->netRefundTotal;

        $this->assertSame($this->netRefundTotal, $actual);
    }
}
