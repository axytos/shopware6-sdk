<?php declare(strict_types=1);

namespace Axytos\Shopware\DataMapping;

use Axytos\ECommerce\DataTransferObjects\ReturnPositionModelDtoCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;

class ReturnPositionModelDtoCollectionFactory
{
    private ReturnPositionModelDtoFactory $returnPositionModelDtoFactory;

    public function __construct(ReturnPositionModelDtoFactory $returnPositionModelDtoFactory)
    {
        $this->returnPositionModelDtoFactory = $returnPositionModelDtoFactory;
    }

    public function create(?OrderLineItemCollection $orderLineItemCollection): ReturnPositionModelDtoCollection
    {
        if (is_null($orderLineItemCollection))
        {
            return new ReturnPositionModelDtoCollection();
        }

        $positons = array_values($orderLineItemCollection->map(function(OrderLineItemEntity $orderLineItemEntity){
            return $this->returnPositionModelDtoFactory->create($orderLineItemEntity);
        }));

        return new ReturnPositionModelDtoCollection(...$positons);
    }
}