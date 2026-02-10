<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Pricing;

use App\DTOs\PriceCalculationDTO;
use App\Services\Pricing\Strategies\CustomerTypeDiscountStrategy;
use PHPUnit\Framework\TestCase;

class CustomerTypeDiscountStrategyTest extends TestCase
{
    private CustomerTypeDiscountStrategy $strategy;

    protected function setUp(): void
    {
        $this->strategy = new CustomerTypeDiscountStrategy();
    }

    public function test_no_discount_for_varejo(): void
    {
        $dto = $this->makeDto('varejo');
        $this->assertEquals(0.0, $this->strategy->calculate($dto));
    }

    public function test_discount_for_atacado(): void
    {
        $dto = $this->makeDto('atacado');
        $this->assertEquals(5.0, $this->strategy->calculate($dto));
    }

    public function test_discount_for_revendedor(): void
    {
        $dto = $this->makeDto('revendedor');
        $this->assertEquals(8.0, $this->strategy->calculate($dto));
    }

    private function makeDto(string $customerType): PriceCalculationDTO
    {
        return new PriceCalculationDTO(
            basePrice: 100.0,
            quantity: 1,
            customerType: $customerType,
            state: 'SP',
            weightKg: 5.0,
        );
    }
}
