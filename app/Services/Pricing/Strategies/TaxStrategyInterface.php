<?php

declare(strict_types=1);

namespace App\Services\Pricing\Strategies;

interface TaxStrategyInterface
{
    /**
     * Retorna a alíquota de imposto para o estado (ex: 18.0 = 18%)
     */
    public function getRateForState(string $state): float;
}
