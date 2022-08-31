<?php

declare(strict_types=1);

namespace Axytos\Shopware\DataMapping;

use Axytos\ECommerce\DataTransferObjects\CreateInvoiceBasketPositionDto;
use Axytos\Shopware\ValueCalculation\PositionGrossPricePerUnitCalculator;
use Axytos\Shopware\ValueCalculation\PositionNetPriceCalculator;
use Axytos\Shopware\ValueCalculation\PositionNetPricePerUnitCalculator;
use Axytos\Shopware\ValueCalculation\PositionProductIdCalculator;
use Axytos\Shopware\ValueCalculation\PositionProductNameCalculator;
use Axytos\Shopware\ValueCalculation\PositionTaxPercentCalculator;
use LogicException;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEntity;

class CreateInvoiceBasketPositionDtoFactory
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

    public function create(OrderLineItemEntity $orderLineItemEntity): CreateInvoiceBasketPositionDto
    {

        $createInvoiceBasketPosition = new CreateInvoiceBasketPositionDto();
        $createInvoiceBasketPosition->grossPositionTotal = $orderLineItemEntity->getTotalPrice();
        $createInvoiceBasketPosition->quantity = $orderLineItemEntity->getQuantity();
        $createInvoiceBasketPosition->productId = $this->positionProductIdCalculator->calculate($orderLineItemEntity);
        $createInvoiceBasketPosition->productName = $this->positionProductNameCalculator->calculate($orderLineItemEntity);
        $createInvoiceBasketPosition->grossPricePerUnit = $this->positionGrossPricePerUnitCalculator->calculate($orderLineItemEntity->getPrice());
        $createInvoiceBasketPosition->netPositionTotal = $this->positionNetPriceCalculator->calculate($orderLineItemEntity->getPrice());
        $createInvoiceBasketPosition->netPricePerUnit = $this->positionNetPricePerUnitCalculator->calculate($orderLineItemEntity->getPrice());
        $createInvoiceBasketPosition->taxPercent = $this->positionTaxPercentCalculator->calculate($orderLineItemEntity->getPrice());

        return $createInvoiceBasketPosition;
    }

    public function createShippingPosition(OrderEntity $orderEntity): CreateInvoiceBasketPositionDto
    {
        $shippingPosition = new CreateInvoiceBasketPositionDto();
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
