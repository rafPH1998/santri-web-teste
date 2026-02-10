<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Pricing;

use App\DTOs\PriceCalculationDTO;
use App\Services\Pricing\Strategies\QuantityDiscountStrategy;
use PHPUnit\Framework\TestCase;

class QuantityDiscountStrategyTest extends TestCase
{
    private QuantityDiscountStrategy $strategy;

    protected function setUp(): void
    {
        $this->strategy = new QuantityDiscountStrategy();
    }

    public function test_no_discount_for_small_quantities(): void
    {
        $dto = $this->makeDto(quantity: 1);
        $this->assertEquals(0.0, $this->strategy->calculate($dto));

        $dto = $this->makeDto(quantity: 9);
        $this->assertEquals(0.0, $this->strategy->calculate($dto));
    }

    public function test_three_percent_for_medium_quantities(): void
    {
        $dto = $this->makeDto(quantity: 10);
        $this->assertEquals(3.0, $this->strategy->calculate($dto));

        $dto = $this->makeDto(quantity: 49);
        $this->assertEquals(3.0, $this->strategy->calculate($dto));
    }

    public function test_five_percent_for_large_quantities(): void
    {
        $dto = $this->makeDto(quantity: 50);
        $this->assertEquals(5.0, $this->strategy->calculate($dto));

        $dto = $this->makeDto(quantity: 200);
        $this->assertEquals(5.0, $this->strategy->calculate($dto));
    }

    public function test_boundary_at_exactly_10(): void
    {
        $dto = $this->makeDto(quantity: 10);
        $this->assertEquals(3.0, $this->strategy->calculate($dto));
    }

    public function test_boundary_at_exactly_50(): void
    {
        $dto = $this->makeDto(quantity: 50);
        $this->assertEquals(5.0, $this->strategy->calculate($dto));
    }

    public function test_strategy_name(): void
    {
        $this->assertEquals('quantity_discount', $this->strategy->getName());
    }

    private function makeDto(int $quantity): PriceCalculationDTO
    {
        return new PriceCalculationDTO(
            basePrice: 100.0,
            quantity: $quantity,
            customerType: 'varejo',
            state: 'SP',
            weightKg: 10.0,
        );
    }
}
