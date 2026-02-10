<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Pricing;

use App\DTOs\PriceCalculationDTO;
use App\Exceptions\InvalidCalculationDataException;
use PHPUnit\Framework\TestCase;

class PriceCalculationDTOTest extends TestCase
{
    public function test_creates_from_valid_array(): void
    {
        $dto = PriceCalculationDTO::fromArray([
            'base_price'    => 100.0,
            'quantity'      => 5,
            'customer_type' => 'varejo',
            'state'         => 'SP',
            'weight_kg'     => 10.0,
        ]);

        $this->assertEquals(100.0, $dto->basePrice);
        $this->assertEquals(5, $dto->quantity);
        $this->assertEquals('varejo', $dto->customerType);
        $this->assertEquals('SP', $dto->state);
        $this->assertFalse($dto->isPremium);
        $this->assertEquals(0.0, $dto->profitMargin);
    }

    public function test_state_is_uppercased(): void
    {
        $dto = PriceCalculationDTO::fromArray([
            'base_price'    => 100.0,
            'quantity'      => 1,
            'customer_type' => 'varejo',
            'state'         => 'sp',  // lowercase
            'weight_kg'     => 5.0,
        ]);

        $this->assertEquals('SP', $dto->state);
    }

    public function test_throws_on_negative_price(): void
    {
        $this->expectException(InvalidCalculationDataException::class);

        new PriceCalculationDTO(
            basePrice: -10.0,
            quantity: 1,
            customerType: 'varejo',
            state: 'SP',
            weightKg: 5.0,
        );
    }

    public function test_throws_on_zero_quantity(): void
    {
        $this->expectException(InvalidCalculationDataException::class);

        new PriceCalculationDTO(
            basePrice: 100.0,
            quantity: 0,
            customerType: 'varejo',
            state: 'SP',
            weightKg: 5.0,
        );
    }

    public function test_throws_on_invalid_customer_type(): void
    {
        $this->expectException(InvalidCalculationDataException::class);

        new PriceCalculationDTO(
            basePrice: 100.0,
            quantity: 1,
            customerType: 'pessoa_fisica', // inválido
            state: 'SP',
            weightKg: 5.0,
        );
    }

    public function test_throws_on_missing_required_field(): void
    {
        $this->expectException(InvalidCalculationDataException::class);

        PriceCalculationDTO::fromArray([
            'base_price' => 100.0,
            // quantity faltando
            'customer_type' => 'varejo',
            'state'         => 'SP',
            'weight_kg'     => 5.0,
        ]);
    }

    public function test_optional_fields_have_defaults(): void
    {
        $dto = PriceCalculationDTO::fromArray([
            'base_price'    => 50.0,
            'quantity'      => 2,
            'customer_type' => 'atacado',
            'state'         => 'RJ',
            'weight_kg'     => 0.5,
        ]);

        $this->assertFalse($dto->isPremium);
        $this->assertEquals(0.0, $dto->profitMargin);
    }
}
