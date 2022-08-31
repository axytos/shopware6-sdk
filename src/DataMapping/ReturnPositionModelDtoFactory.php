<?php

declare(strict_types=1);

namespace Axytos\Shopware\DataMapping;

use Axytos\ECommerce\DataTransferObjects\ReturnPositionModelDto;
use Axytos\Shopware\ValueCalculation\PositionProductIdCalculator;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;

class ReturnPositionModelDtoFactory
{
    private PositionProductIdCalculator $positionProductIdCalcualtor;

    public function __construct(PositionProductIdCalculator $positionProductIdCalcualtor)
    {
        $this->positionProductIdCalcualtor = $positionProductIdCalcualtor;
    }

    public function create(OrderLineItemEntity $orderLineItemEntity): ReturnPositionModelDto
    {
        $position = new ReturnPositionModelDto();
        $position->quantityToReturn = $orderLineItemEntity->getQuantity();
        $position->productId = $this->positionProductIdCalcualtor->calculate($orderLineItemEntity);
        return $position;
    }
}
