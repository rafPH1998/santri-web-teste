<?php

declare(strict_types=1);

namespace App\Services\Pricing\Strategies;

/**
 * Alíquotas de ICMS por estado.
 * Valores aproximados - em produção isso viria do banco ou de um serviço externo.
 */
class IcmsTaxStrategy implements TaxStrategyInterface
{
    private array $rates = [
        'SP' => 18.0,
        'RJ' => 20.0,
        'MG' => 18.0,
        'RS' => 17.0,
        'PR' => 17.5,
        'SC' => 17.0,
        'BA' => 19.0,
        'PE' => 18.0,
        'CE' => 18.0,
        'GO' => 17.0,
        'DF' => 18.0,
        'ES' => 17.0,
        'MT' => 17.0,
        'MS' => 17.0,
        'PA' => 17.0,
        'AM' => 20.0,
        'MA' => 18.0,
        'PB' => 18.0,
        'RN' => 18.0,
        'AL' => 19.0,
        'SE' => 19.0,
        'PI' => 18.0,
        'TO' => 18.0,
        'RO' => 17.5,
        'AC' => 17.0,
        'AP' => 18.0,
        'RR' => 17.0,
    ];

    // alíquota padrão caso o estado não esteja na lista
    private float $defaultRate = 18.0;

    public function getRateForState(string $state): float
    {
        $state = strtoupper($state);
        return $this->rates[$state] ?? $this->defaultRate;
    }
}
