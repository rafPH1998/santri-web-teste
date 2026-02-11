<?php

declare(strict_types=1);

namespace App\Services\Pricing\Strategies;

use App\DTOs\PriceCalculationDTO;

/**
 * Desconto baseado no tipo de cliente.
 * Varejo: 0%, Atacado: 5%, Revendedor: 8%
 */
class CustomerTypeDiscountStrategy implements DiscountStrategyInterface
{
    // Tabela de descontos por tipo de cliente (%)
    private array $discountTable = [
        'varejo'     => 0.0,
        'atacado'    => 5.0,
        'revendedor' => 8.0,
    ];

    public function calculate(PriceCalculationDTO $dto): float
    {
        return $this->discountTable[$dto->customerType] ?? 0.0;
    }

    public function getName(): string
    {
        return 'customer_type_discount';
    }
}
