<?php

declare(strict_types=1);

namespace Axytos\Shopware\Tests\DataMapping;

use Axytos\Shopware\DataMapping\ReturnPositionModelDtoFactory;
use Axytos\Shopware\ValueCalculation\PositionProductIdCalculator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;

class ReturnPositionModelDtoFactoryTest extends TestCase
{
    /** @var PositionProductIdCalculator&MockObject */
    private PositionProductIdCalculator $positionProductIdCalculator;

    private ReturnPositionModelDtoFactory $sut;

    public function setUp(): void
    {
        $this->positionProductIdCalculator = $this->createMock(PositionProductIdCalculator::class);

        $this->sut = new ReturnPositionModelDtoFactory(
            $this->positionProductIdCalculator
        );
    }

    public function test_create_maps_quantity(): void
    {
        /** @var OrderLineItemEntity&MockObject */
        $orderLineItem = $this->createMock(OrderLineItemEntity::class);
        $orderLineItem->method('getQuantity')->willReturn(5);

        $actual = $this->sut->create($orderLineItem);

        $this->assertEquals($orderLineItem->getQuantity(), $actual->quantityToReturn);
    }

    public function test_create_calculates_product_id(): void
    {
        /** @var OrderLineItemEntity&MockObject */
        $orderLineItem = $this->createMock(OrderLineItemEntity::class);

        $productId = 'productId';
        $this->positionProductIdCalculator
            ->method('calculate')
            ->with($orderLineItem)
            ->willReturn($productId);

        $actual = $this->sut->create($orderLineItem);

        $this->assertEquals($productId, $actual->productId);
    }
}
