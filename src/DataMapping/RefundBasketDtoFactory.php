<?php declare(strict_types=1);

namespace Axytos\Shopware\DataMapping;

use Axytos\ECommerce\DataTransferObjects\RefundBasketDto;
use Shopware\Core\Checkout\Order\OrderEntity;

class RefundBasketDtoFactory
{
    private RefundBasketPositionDtoCollectionFactory $refundBasketPositionDtoCollectionFactory;
    private RefundBasketTaxGroupDtoCollectionFactory $refundBasketTaxGroupDtoCollectionFactory;

    public function __construct(RefundBasketPositionDtoCollectionFactory $refundBasketPositionDtoCollectionFactory, RefundBasketTaxGroupDtoCollectionFactory $refundBasketTaxGroupDtoCollectionFactory)
    {
        $this->refundBasketPositionDtoCollectionFactory = $refundBasketPositionDtoCollectionFactory;
        $this->refundBasketTaxGroupDtoCollectionFactory = $refundBasketTaxGroupDtoCollectionFactory;
    }

    public function create(OrderEntity $orderEntity): RefundBasketDto
    {
        $refundBasket = new RefundBasketDto();
        $refundBasket->grossTotal = $orderEntity->getAmountTotal();
        $refundBasket->netTotal = $orderEntity->getAmountNet();
        $refundBasket->positions = $this->refundBasketPositionDtoCollectionFactory->create($orderEntity->getLineItems());
        $refundBasket->taxGroups = $this->refundBasketTaxGroupDtoCollectionFactory->create($orderEntity->getPrice()->getCalculatedTaxes());
        return $refundBasket;
    }
}
