<?php declare(strict_types=1);

namespace Axytos\Shopware\DataAbstractionLayer;

use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryDefinition;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionDefinition;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineTransition\StateMachineTransitionActions;
use Shopware\Core\System\StateMachine\StateMachineRegistry;
use Shopware\Core\System\StateMachine\Transition;

class OrderEntityRepository
{
    private EntityRepositoryInterface $orderRepository;
    private StateMachineRegistry $stateMachineRegistry;

    public function __construct(
        EntityRepositoryInterface $orderRepository,
        StateMachineRegistry $stateMachineRegistry)
    {
        $this->orderRepository = $orderRepository;
        $this->stateMachineRegistry = $stateMachineRegistry;
    }

    public function findOrder(string $orderId, Context $context): OrderEntity
    {
        $criteria = new Criteria([$orderId]);
        $criteria->addAssociation('transactions.stateMachineState');
        $criteria->addAssociation('transactions.paymentMethod');
        $criteria->addAssociation('orderCustomer.customer');
        $criteria->addAssociation('orderCustomer.salutation');
        $criteria->addAssociation('salutation');
        $criteria->addAssociation('transactions.paymentMethod.appPaymentMethod.app');
        $criteria->addAssociation('language');
        $criteria->addAssociation('currency');
        $criteria->addAssociation('deliveries.shippingOrderAddress.country');
        $criteria->addAssociation('deliveries.shippingOrderAddress.countryState');
        $criteria->addAssociation('deliveries.shippingOrderAddress.salutation');
        $criteria->addAssociation('billingAddress.country');
        $criteria->addAssociation('billingAddress.countryState');
        $criteria->addAssociation('billingAddress.salutation');
        $criteria->addAssociation('lineItems');
        $criteria->addAssociation('lineItems.product');
        $criteria->addAssociation('lineItems.promotion');
        $criteria->getAssociation('transactions');

        return $this->findFirst($criteria, $context);
    }

    public function getCustomFields(string $orderId, Context $context): array
    {
        $criteria = new Criteria([$orderId]);
        $criteria->addAssociation('customFields');

        $orderEntity = $this->findFirst($criteria, $context);

        if (is_null($orderEntity->getCustomFields()))
        {
            return [];
        }

        return $orderEntity->getCustomFields();
    }

    public function updateCustomFields(string $orderId, array $customFields, Context $context): void
    {
        $orderData = [
            'id' => $orderId,
            'customFields' => $customFields
        ];
        $this->orderRepository->update([$orderData], $context);
    }

    public function cancelOrder(string $orderId, Context $context): void
    {
        $criteria = new Criteria([$orderId]);
        $criteria->addAssociation('transactions');
        $criteria->addAssociation('deliveries');
        $criteria->setLimit(1);
        $order = $this->orderRepository->search($criteria, $context)->first();
        
        $this->stateMachineRegistry->transition(new Transition(
            OrderDefinition::ENTITY_NAME,
            $order->getId(),
            StateMachineTransitionActions::ACTION_CANCEL,
            'stateId'
        ), $context);

        foreach($order->getTransactions() as $orderTransaction)
        {
            $this->stateMachineRegistry->transition(new Transition(
                OrderTransactionDefinition::ENTITY_NAME,
                $orderTransaction->getId(),
                StateMachineTransitionActions::ACTION_CANCEL,
                'stateId'
            ), $context);
        }

        foreach($order->getDeliveries() as $orderDelivery)
        {
            $this->stateMachineRegistry->transition(new Transition(
                OrderDeliveryDefinition::ENTITY_NAME,
                $orderDelivery->getId(),
                StateMachineTransitionActions::ACTION_CANCEL,
                'stateId'
            ), $context);
        }
    }

    public function failPayment(string $orderId, Context $context): void
    {
        $criteria = new Criteria([$orderId]);
        $criteria->addAssociation('transactions');
        $criteria->setLimit(1);
        $order = $this->orderRepository->search($criteria, $context)->first();

        foreach($order->getTransactions() as $orderTransaction)
        {
            $this->stateMachineRegistry->transition(new Transition(
                OrderTransactionDefinition::ENTITY_NAME,
                $orderTransaction->getId(),
                StateMachineTransitionActions::ACTION_FAIL,
                'stateId'
            ), $context);
        }
    }

    private function findFirst(Criteria $criteria, Context $context): OrderEntity
    {
        /** @var EntityFinder<OrderEntity> */
        $entityFinder = new EntityFinder($this->orderRepository);
        return $entityFinder->findFirst($criteria, $context);
    }

    public function payOrder(string $orderId, Context $context): void
    {
        $this->stateMachineRegistry->transition(new Transition(
            OrderDefinition::ENTITY_NAME,
            $orderId,
            StateMachineTransitionActions::ACTION_PAID,
            'stateId'
        ), $context);
    }

    public function payOrderPartially(string $orderId, Context $context): void
    {
        $this->stateMachineRegistry->transition(new Transition(
            OrderDefinition::ENTITY_NAME,
            $orderId,
            StateMachineTransitionActions::ACTION_PAID_PARTIALLY,
            'stateId'
        ), $context);
    }
}