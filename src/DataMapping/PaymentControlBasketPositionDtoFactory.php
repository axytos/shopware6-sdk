<?php

declare(strict_types=1);

namespace Axytos\Shopware\DataMapping;

use Axytos\ECommerce\DataTransferObjects\PaymentControlBasketPositionDto;
use Axytos\Shopware\ValueCalculation\PositionNetPriceCalculator;
use Axytos\Shopware\ValueCalculation\PositionProductIdCalculator;
use Axytos\Shopware\ValueCalculation\PositionProductNameCalculator;
use Axytos\Shopware\ValueCalculation\PositionTaxPercentCalculator;
use LogicException;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEntity;

class PaymentControlBasketPositionDtoFactory
{
    private PositionNetPriceCalculator $positionNetPriceCalculator;
    private PositionTaxPercentCalculator $positionTaxPercentCalculator;
    private PositionProductIdCalculator $positionProductIdCalculator;
    private PositionProductNameCalculator $positionProductNameCalculator;

    public function __construct(
        PositionNetPriceCalculator $positionNetPriceCalculator,
        PositionTaxPercentCalculator $positionTaxPercentCalculator,
        PositionProductIdCalculator $positionProductIdCalculator,
        PositionProductNameCalculator $positionProductNameCalculator
    ) {
        $this->positionNetPriceCalculator = $positionNetPriceCalculator;
        $this->positionTaxPercentCalculator = $positionTaxPercentCalculator;
        $this->positionProductIdCalculator = $positionProductIdCalculator;
        $this->positionProductNameCalculator = $positionProductNameCalculator;
    }

    public function create(OrderLineItemEntity $orderLineItemEntity): PaymentControlBasketPositionDto
    {
        $basketPosition = new PaymentControlBasketPositionDto();
        $basketPosition->grossPositionTotal = $orderLineItemEntity->getTotalPrice();
        $basketPosition->quantity = $orderLineItemEntity->getQuantity();
        $basketPosition->productId = $this->positionProductIdCalculator->calculate($orderLineItemEntity);
        $basketPosition->productName = $this->positionProductNameCalculator->calculate($orderLineItemEntity);
        $basketPosition->netPositionTotal = $this->positionNetPriceCalculator->calculate($orderLineItemEntity->getPrice());
        $basketPosition->taxPercent = $this->positionTaxPercentCalculator->calculate($orderLineItemEntity->getPrice());

        return $basketPosition;
    }

    public function createShippingPosition(OrderEntity $orderEntity): PaymentControlBasketPositionDto
    {
        $shippingPosition = new PaymentControlBasketPositionDto();
        $shippingPosition->productId = '0';
        $shippingPosition->productName = 'Shipping';
        $shippingPosition->quantity = 1;
        $shippingPosition->grossPositionTotal = $orderEntity->getShippingTotal();
        $shippingPosition->netPositionTotal = $this->positionNetPriceCalculator->calculate($orderEntity->getShippingCosts());
        $shippingPosition->taxPercent = $this->positionTaxPercentCalculator->calculate($orderEntity->getShippingCosts());

        return $shippingPosition;
    }
}
