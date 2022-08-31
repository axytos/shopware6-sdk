<?php

declare(strict_types=1);

namespace Axytos\Shopware\DataMapping;

use Axytos\ECommerce\DataTransferObjects\BasketPositionDto;
use Axytos\Shopware\ValueCalculation\PositionGrossPricePerUnitCalculator;
use Axytos\Shopware\ValueCalculation\PositionNetPriceCalculator;
use Axytos\Shopware\ValueCalculation\PositionNetPricePerUnitCalculator;
use Axytos\Shopware\ValueCalculation\PositionProductIdCalculator;
use Axytos\Shopware\ValueCalculation\PositionProductNameCalculator;
use Axytos\Shopware\ValueCalculation\PositionTaxPercentCalculator;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEntity;

class BasketPositionDtoFactory
{
    private PositionNetPriceCalculator $positionNetPriceCalculator;
    private PositionTaxPercentCalculator $positionTaxPercentCalculator;
    private PositionNetPricePerUnitCalculator $positionNetPricePerUnitCalculator;
    private PositionGrossPricePerUnitCalculator $positionGrossPricePerUnitCalculator;
    private PositionProductIdCalculator $positionProductIdCalculator;
    private PositionProductNameCalculator $positionProductNameCalculator;

    public function __construct(
        PositionNetPriceCalculator $positionNetPriceCalculator,
        PositionTaxPercentCalculator $positionTaxPercentCalculator,
        PositionNetPricePerUnitCalculator $positionNetPricePerUnitCalculator,
        PositionGrossPricePerUnitCalculator $positionGrossPricePerUnitCalculator,
        PositionProductIdCalculator $positionProductIdCalculator,
        PositionProductNameCalculator $positionProductNameCalculator
    ) {
        $this->positionNetPriceCalculator = $positionNetPriceCalculator;
        $this->positionTaxPercentCalculator = $positionTaxPercentCalculator;
        $this->positionNetPricePerUnitCalculator = $positionNetPricePerUnitCalculator;
        $this->positionGrossPricePerUnitCalculator = $positionGrossPricePerUnitCalculator;
        $this->positionProductIdCalculator = $positionProductIdCalculator;
        $this->positionProductNameCalculator = $positionProductNameCalculator;
    }

    public function create(OrderLineItemEntity $orderLineItemEntity): BasketPositionDto
    {
        $basketPosition = new BasketPositionDto();
        $basketPosition->productId = $this->positionProductIdCalculator->calculate($orderLineItemEntity);
        $basketPosition->productName = $this->positionProductNameCalculator->calculate($orderLineItemEntity);
        $basketPosition->quantity = $orderLineItemEntity->getQuantity();
        $basketPosition->grossPositionTotal = $orderLineItemEntity->getTotalPrice();
        $basketPosition->netPositionTotal = $this->positionNetPriceCalculator->calculate($orderLineItemEntity->getPrice());
        $basketPosition->taxPercent = $this->positionTaxPercentCalculator->calculate($orderLineItemEntity->getPrice());
        $basketPosition->netPricePerUnit = $this->positionNetPricePerUnitCalculator->calculate($orderLineItemEntity->getPrice());
        $basketPosition->grossPricePerUnit = $this->positionGrossPricePerUnitCalculator->calculate($orderLineItemEntity->getPrice());

        return $basketPosition;
    }

    public function createShippingPosition(OrderEntity $orderEntity): BasketPositionDto
    {
        $shippingPosition = new BasketPositionDto();
        $shippingPosition->productId = '0';
        $shippingPosition->productName = 'Shipping';
        $shippingPosition->quantity = 1;
        $shippingPosition->grossPositionTotal = $orderEntity->getShippingTotal();
        $shippingPosition->netPositionTotal = $this->positionNetPriceCalculator->calculate($orderEntity->getShippingCosts());
        $shippingPosition->taxPercent = $this->positionTaxPercentCalculator->calculate($orderEntity->getShippingCosts());
        $shippingPosition->netPricePerUnit = $this->positionNetPricePerUnitCalculator->calculate($orderEntity->getShippingCosts());
        $shippingPosition->grossPricePerUnit = $this->positionGrossPricePerUnitCalculator->calculate($orderEntity->getShippingCosts());

        return $shippingPosition;
    }
}
