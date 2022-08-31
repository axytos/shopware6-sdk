<?php

declare(strict_types=1);

namespace Axytos\Shopware\DataMapping;

use Axytos\ECommerce\DataTransferObjects\PaymentControlBasketDto;
use Shopware\Core\Checkout\Order\OrderEntity;

class PaymentControlBasketDtoFactory
{
    private PaymentControlBasketPositionDtoCollectionFactory $basketPositionCollectionFactory;

    public function __construct(PaymentControlBasketPositionDtoCollectionFactory $basketPositionCollectionFactory)
    {
        $this->basketPositionCollectionFactory = $basketPositionCollectionFactory;
    }

    public function create(OrderEntity $orderEntity): PaymentControlBasketDto
    {
        $basket = new PaymentControlBasketDto();

        $currency = $orderEntity->getCurrency();

        if (!is_null($currency)) {
            $basket->currency = $currency->getIsoCode();
        }

        $basket->grossTotal = $orderEntity->getAmountTotal();
        $basket->netTotal = $orderEntity->getAmountNet();
        $basket->positions = $this->basketPositionCollectionFactory->create($orderEntity);

        return $basket;
    }
}
