<?php declare(strict_types=1);

namespace Axytos\Shopware\Tests\ValueCalculation;

use Axytos\Shopware\ValueCalculation\PositionTaxPercentCalculator;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTax;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;

class PositionTaxPercentCalculatorTest extends TestCase
{
    private PositionTaxPercentCalculator $sut;

    public function setUp(): void
    {
        $this->sut = new PositionTaxPercentCalculator();
    }

    public function test_calculate_returns_zero_if_calculated_price_is_null(): void
    {
        $this->assertEquals(0, $this->sut->calculate(null));
    }

    /**
     * @dataProvider dataProvider_test_calculate_returns_sum_of_caclulated_taxes
     */
    public function test_calculate_returns_sum_of_caclulated_taxes(array $taxRates, float $expectedTaxPercent): void
    {
        $calculatedTaxes = $this->createCalculatedTaxes($taxRates);

        /** @var CalculatedPrice&MockObject */
        $calculatedPrice = $this->createMock(CalculatedPrice::class);
        $calculatedPrice->method('getCalculatedTaxes')->willReturn($calculatedTaxes);

        $actual = $this->sut->calculate($calculatedPrice);

        $this->assertEquals($expectedTaxPercent, $actual);
    }

    public function dataProvider_test_calculate_returns_sum_of_caclulated_taxes(): array
    {
        return [
            [[], 0],
            [[1], 1],
            [[1,2], 3],
            [[1,2,0.5], 3.5],
        ];
    }

    private function createCalculatedTaxes(array $taxRates): CalculatedTaxCollection
    {
        $elements = array_map([$this, 'createCalculatedTax'], $taxRates);
        return new CalculatedTaxCollection($elements);
    }

    private function createCalculatedTax(float $taxRate): CalculatedTax
    {
        $calculatedTax = new CalculatedTax(0, $taxRate, 0);
        return $calculatedTax;
    }
}