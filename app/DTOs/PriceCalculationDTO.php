<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Exceptions\InvalidCalculationDataException;

class PriceCalculationDTO
{
    public function __construct(
        public readonly float $basePrice,
        public readonly int $quantity,
        public readonly string $customerType, // varejo, atacado, revendedor
        public readonly string $state,        // SP, RJ, MG, etc.
        public readonly float $weightKg,
        public readonly bool $isPremium = false,
        public readonly float $profitMargin = 0.0, // percentual ex: 15.5 = 15.5%
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->basePrice <= 0) {
            throw new InvalidCalculationDataException('Preço base deve ser maior que zero');
        }

        if ($this->quantity <= 0) {
            throw new InvalidCalculationDataException('Quantidade deve ser maior que zero');
        }

        $allowedTypes = ['varejo', 'atacado', 'revendedor'];
        if (!in_array($this->customerType, $allowedTypes, true)) {
            throw new InvalidCalculationDataException(
                'Tipo de cliente inválido. Aceitos: ' . implode(', ', $allowedTypes)
            );
        }

        if ($this->weightKg < 0) {
            throw new InvalidCalculationDataException('Peso não pode ser negativo');
        }

        if ($this->profitMargin < 0) {
            throw new InvalidCalculationDataException('Margem de lucro não pode ser negativa');
        }
    }

    public static function fromArray(array $data): self
    {
        $required = ['base_price', 'quantity', 'customer_type', 'state', 'weight_kg'];

        foreach ($required as $field) {
            if (!array_key_exists($field, $data)) {
                throw new InvalidCalculationDataException("Campo obrigatório ausente: {$field}");
            }
        }

        return new self(
            basePrice: (float) $data['base_price'],
            quantity: (int) $data['quantity'],
            customerType: (string) $data['customer_type'],
            state: strtoupper((string) $data['state']),
            weightKg: (float) $data['weight_kg'],
            isPremium: (bool) ($data['is_premium'] ?? false),
            profitMargin: (float) ($data['profit_margin'] ?? 0.0),
        );
    }
}
