<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Pricing;

use App\DTOs\PriceCalculationDTO;
use App\Services\Pricing\Strategies\PremiumDiscountStrategy;
use PHPUnit\Framework\TestCase;

class PremiumDiscountStrategyTest extends TestCase
{
    private PremiumDiscountStrategy $strategy;

    protected function setUp(): void
    {
        $this->strategy = new PremiumDiscountStrategy();
    }

    public function test_no_discount_for_non_premium(): void
    {
        $dto = new PriceCalculationDTO(
            basePrice: 100.0,
            quantity: 1,
            customerType: 'varejo',
            state: 'SP',
            weightKg: 5.0,
            isPremium: false,
        );

        $this->assertEquals(0.0, $this->strategy->calculate($dto));
    }

    public function test_two_percent_for_premium(): void
    {
        $dto = new PriceCalculationDTO(
            basePrice: 100.0,
            quantity: 1,
            customerType: 'varejo',
            state: 'SP',
            weightKg: 5.0,
            isPremium: true,
        );

        $this->assertEquals(2.0, $this->strategy->calculate($dto));
    }
}
