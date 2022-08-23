<?php declare(strict_types=1);

namespace Axytos\Shopware\Tests\ValueCalculation;

use Axytos\Shopware\ValueCalculation\PromotionIdentifierCalculator;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Promotion\PromotionEntity;

class PromotionIdentifierCalculatorTest extends TestCase
{
    private PromotionIdentifierCalculator $sut;

    public function setUp(): void
    {
        $this->sut = new PromotionIdentifierCalculator();
    }

    /**
     * @dataProvider dataProvider_test_calculate
     */
    public function test_calculate(string $promotionName, string $promotionCode, string $expectedIdentifier): void
    {
        /** @var PromotionEntity&MockObject */
        $promotionEntity = $this->createMock(PromotionEntity::class);
        $promotionEntity->method('getName')->willReturn($promotionName); 

        /** @var OrderLineItemEntity&MockObject */
        $orderLineItemEntity = $this->createMock(OrderLineItemEntity::class);
        $orderLineItemEntity->method('getPromotion')->willReturn($promotionEntity);
        $orderLineItemEntity->method('getReferencedId')->willReturn($promotionCode);

        $actualIdentifier = $this->sut->calculate($orderLineItemEntity);

        $this->assertEquals($expectedIdentifier, $actualIdentifier);
    }

    public function dataProvider_test_calculate(): array
    {
        return [
            ['', '', ' '],
            ['PromotionName', 'PromotionCode', 'PromotionName PromotionCode'],
        ];
    }
}