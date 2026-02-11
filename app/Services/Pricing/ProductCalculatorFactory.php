<?php

declare(strict_types=1);

namespace App\Services\Pricing;

use App\Services\Pricing\Cache\LaravelCacheAdapter;
use App\Services\Pricing\Modifiers\FreightModifier;
use App\Services\Pricing\Strategies\CustomerTypeDiscountStrategy;
use App\Services\Pricing\Strategies\IcmsTaxStrategy;
use App\Services\Pricing\Strategies\PremiumDiscountStrategy;
use App\Services\Pricing\Strategies\QuantityDiscountStrategy;

/**
 * Factory que cria um ProductCalculator já configurado com todas as strategies padrão.
 */
class ProductCalculatorFactory
{
    /**
     * Cria um ProductCalculator com todas as estratégias padrão configuradas.
     * Se precisar de um calculator customizado (ex: sem desconto de quantidade),
     * instancie o ProductCalculator diretamente e adicione só o que precisar.
     */
    public static function createDefault(): ProductCalculator
    {
        $calculator = new ProductCalculator(
            taxStrategy: new IcmsTaxStrategy(),
            freightModifier: new FreightModifier(),
            cache: new LaravelCacheAdapter(),
        );

        $calculator
            ->addDiscountStrategy(new QuantityDiscountStrategy())
            ->addDiscountStrategy(new CustomerTypeDiscountStrategy())
            ->addDiscountStrategy(new PremiumDiscountStrategy());

        return $calculator;
    }
}
