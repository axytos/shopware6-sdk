<?php

declare(strict_types=1);

namespace Axytos\Shopware\Order;

use Axytos\Shopware\DataAbstractionLayer\OrderEntityRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class OrderStateMachine
{
    private OrderEntityRepository $orderEntityRepository;

    public function __construct(OrderEntityRepository $orderEntityRepository)
    {
        $this->orderEntityRepository = $orderEntityRepository;
    }

    public function cancelOrder(string $orderId, SalesChannelContext $salesChannelContext): void
    {
        $this->orderEntityRepository->cancelOrder($orderId, $salesChannelContext->getContext());
    }

    public function failPayment(string $orderId, SalesChannelContext $salesChannelContext): void
    {
        $this->orderEntityRepository->failPayment($orderId, $salesChannelContext->getContext());
    }

    public function payOrder(string $orderId, SalesChannelContext $salesChannelContext): void
    {
        $this->orderEntityRepository->payOrder($orderId, $salesChannelContext->getContext());
    }

    public function payOrderPartially(string $orderId, SalesChannelContext $salesChannelContext): void
    {
        $this->orderEntityRepository->payOrderPartially($orderId, $salesChannelContext->getContext());
    }
}
