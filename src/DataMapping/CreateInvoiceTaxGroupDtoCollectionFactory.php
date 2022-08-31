<?php

declare(strict_types=1);

namespace Axytos\Shopware\DataMapping;

use Axytos\ECommerce\DataTransferObjects\CreateInvoiceTaxGroupDtoCollection;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTax;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;

class CreateInvoiceTaxGroupDtoCollectionFactory
{
    private CreateInvoiceTaxGroupDtoFactory $createInvoiceTaxGroupDtoFactory;

    public function __construct(CreateInvoiceTaxGroupDtoFactory $createInvoiceTaxGroupDtoFactory)
    {
        $this->createInvoiceTaxGroupDtoFactory = $createInvoiceTaxGroupDtoFactory;
    }

    public function create(?CalculatedTaxCollection $calculatedTaxCollection = null): CreateInvoiceTaxGroupDtoCollection
    {
        if (is_null($calculatedTaxCollection)) {
            return new CreateInvoiceTaxGroupDtoCollection();
        }

        $positions = array_values($calculatedTaxCollection->map(function (CalculatedTax $calculatedTax) {
            return $this->createInvoiceTaxGroupDtoFactory->create($calculatedTax);
        }));

        $result = new CreateInvoiceTaxGroupDtoCollection(...$positions);

        return $result;
    }
}
