<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Pricing;

use App\DTOs\PriceCalculationDTO;
use App\Services\Pricing\Modifiers\FreightModifier;
use PHPUnit\Framework\TestCase;

class FreightModifierTest extends TestCase
{
    private FreightModifier $modifier;

    protected function setUp(): void
    {
        $this->modifier = new FreightModifier();
    }

    public function test_no_surcharge_for_light_product(): void
    {
        $dto = $this->makeDto(weightKg: 49.9);
        $this->assertEquals(0.0, $this->modifier->getSurcharge($dto));
    }

    public function test_no_surcharge_at_exactly_50kg(): void
    {
        // O limite é ACIMA de 50, então exatamente 50kg não paga
        $dto = $this->makeDto(weightKg: 50.0);
        $this->assertEquals(0.0, $this->modifier->getSurcharge($dto));
    }

    public function test_surcharge_for_heavy_product(): void
    {
        $dto = $this->makeDto(weightKg: 50.01);
        $this->assertEquals(15.0, $this->modifier->getSurcharge($dto));

        $dto = $this->makeDto(weightKg: 100.0);
        $this->assertEquals(15.0, $this->modifier->getSurcharge($dto));
    }

    public function test_zero_weight_no_surcharge(): void
    {
        $dto = $this->makeDto(weightKg: 0.0);
        $this->assertEquals(0.0, $this->modifier->getSurcharge($dto));
    }

    private function makeDto(float $weightKg): PriceCalculationDTO
    {
        return new PriceCalculationDTO(
            basePrice: 100.0,
            quantity: 1,
            customerType: 'varejo',
            state: 'SP',
            weightKg: $weightKg,
        );
    }
}
