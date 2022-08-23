<?php declare(strict_types=1);

namespace Axytos\Shopware\DataMapping;

use Axytos\ECommerce\DataTransferObjects\RefundBasketPositionDtoCollection;
use Axytos\Shopware\DataMapping\RefundBasketPositionDtoFactory;
use LogicException;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Axytos\Shopware\ValueCalculation\PositionNetPriceCalculator;

class RefundBasketPositionDtoCollectionFactory
{
    private RefundBasketPositionDtoFactory $refundBasketPositionDtoFactory;
    private PositionNetPriceCalculator $positionNetPriceCalculator;

    public function __construct(RefundBasketPositionDtoFactory $refundBasketPositionDtoFactory, PositionNetPriceCalculator $positionNetPriceCalculator)
    {
        $this->refundBasketPositionDtoFactory = $refundBasketPositionDtoFactory;
        $this->positionNetPriceCalculator = $positionNetPriceCalculator;
    }

    public function create(?OrderLineItemCollection $orderLineItems = null): RefundBasketPositionDtoCollection
    {
        if (is_null($orderLineItems))
        {
            return new RefundBasketPositionDtoCollection();
        }

        $credits = $orderLineItems->filter(function(OrderLineItemEntity $orderLineItemEntity){
            return $orderLineItemEntity->getType() === LineItem::CREDIT_LINE_ITEM_TYPE;
        });

        $products = $orderLineItems->filter(function(OrderLineItemEntity $orderLineItemEntity){
            return $orderLineItemEntity->getType() === LineItem::PRODUCT_LINE_ITEM_TYPE;
        });

        $groupedCredits = $this->groupLineItemsByTaxRate($credits);

        $positions = [];

        foreach ($groupedCredits as $taxRate => $credits)
        {
            $grossRefundTotal = $this->calculateGrossRefundTotal($credits);
            $netRefundTotal = $this->calculateNetRefundTotal($credits);
            $productNumber = $this->findProductNumberForTaxRate($products, (string) $taxRate);

            $position = $this->refundBasketPositionDtoFactory->create($productNumber, $grossRefundTotal, $netRefundTotal);

            array_push($positions, $position);
        }
        
        $result = new RefundBasketPositionDtoCollection(...$positions);

        return $result;
    }

    /**
     * @param array<OrderLineItemEntity> $orderLineItems
     * @return float
     */
    private function calculateGrossRefundTotal(array $orderLineItems): float
    {
        $grossPrices = array_map(function(OrderLineItemEntity $oli)
        {
            $price = $oli->getPrice();
            if(is_null($price))
            {
                return 0;
            }
            $price->getTotalPrice(); 
        }, $orderLineItems);

        return  (float) array_sum($grossPrices) * -1;
    }

    /**
     * @param array<OrderLineItemEntity> $orderLineItems
     * @return float
     */
    private function calculateNetRefundTotal(array $orderLineItems): float
    {
        $netPrices = array_map(function(OrderLineItemEntity $oli)
        { 
            return $this->positionNetPriceCalculator->calculate($oli->getPrice()); 
        }, $orderLineItems);

        return (float) array_sum($netPrices) * -1;
    }

    /**
     * @return array<string,array<OrderLineItemEntity>>
     */
    private function groupLineItemsByTaxRate(OrderLineItemCollection $orderLineItems): array
    {
        return $orderLineItems->reduce(function(array $carry, OrderLineItemEntity $orderLineItemEntity) {
            $price = $orderLineItemEntity->getPrice();
            if(!is_null($price))
            {
                $calculatedTax = $price->getCalculatedTaxes()->first();
                if(!is_null($calculatedTax))
                {
                    $taxRate = $calculatedTax->getTaxRate();
                    $taxKey = "$taxRate";
        
                    $carry[$taxKey][] = $orderLineItemEntity;
                }
            }

            return $carry;
        }, []);
    }

    private function findProductNumberForTaxRate(OrderLineItemCollection $products, string $taxRate): string
    {
        foreach ($products as $product)
        {
            $price = $product->getPrice();
            if(!is_null($price)) {
                $calculatedTax = $price->getCalculatedTaxes()->first();
                if(!is_null($calculatedTax))
                {
                    if ($calculatedTax->getTaxRate() == $taxRate) {
                        $product = $product->getProduct();
                        if(!is_null($product))
                        {
                            return $product->getProductNumber();
                        }
                    }
                }
            }
        }

        throw new LogicException("No product with taxRate {$taxRate} found!");
    }
}
