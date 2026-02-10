<?php

declare(strict_types=1);

namespace App\Services\Pricing;

use App\DTOs\PriceCalculationDTO;
use App\DTOs\PriceCalculationResult;
use App\Services\Pricing\Cache\LaravelCacheAdapter;
use App\Services\Pricing\Cache\PriceCacheInterface;
use App\Services\Pricing\Modifiers\FreightModifier;
use App\Services\Pricing\Strategies\DiscountStrategyInterface;
use App\Services\Pricing\Strategies\TaxStrategyInterface;

class ProductCalculator
{
    /** @var DiscountStrategyInterface[] */
    private array $discountStrategies = [];

    public function __construct(
        private readonly TaxStrategyInterface $taxStrategy,
        private readonly FreightModifier $freightModifier,
        private readonly PriceCacheInterface $cache,
    ) {}

    public function addDiscountStrategy(DiscountStrategyInterface $strategy): self
    {
        $this->discountStrategies[] = $strategy;
        return $this;
    }

    public function calculate(PriceCalculationDTO $dto): PriceCalculationResult
    {
        $cacheKey = LaravelCacheAdapter::buildKey([
            'base_price'    => $dto->basePrice,
            'quantity'      => $dto->quantity,
            'customer_type' => $dto->customerType,
            'state'         => $dto->state,
            'weight_kg'     => $dto->weightKg,
            'is_premium'    => $dto->isPremium,
            'profit_margin' => $dto->profitMargin,
        ]);

        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $this->resultFromArray($cached);
        }

        $result = $this->doCalculate($dto);

        $this->cache->set($cacheKey, $result->toArray(), 300);

        return $result;
    }

    private function doCalculate(PriceCalculationDTO $dto): PriceCalculationResult
    {
        $breakdown = [];

        // 1. Aplica a margem de lucro sobre o preço base
        $priceWithMargin = $dto->basePrice;
        if ($dto->profitMargin > 0) {
            $priceWithMargin = $dto->basePrice * (1 + $dto->profitMargin / 100);
        }
        $breakdown[] = "Preço base: R$ " . number_format($dto->basePrice, 2, ',', '.');
        $breakdown[] = "Após margem ({$dto->profitMargin}%): R$ " . number_format($priceWithMargin, 2, ',', '.');

        // 2. Aplica descontos — cada estratégia retorna um percentual
        //    Os descontos são somados e aplicados de uma vez (não composto)
        $totalDiscountPercent = 0.0;
        $discountAmounts = [];

        foreach ($this->discountStrategies as $strategy) {
            $percent = $strategy->calculate($dto);
            $discountAmounts[$strategy->getName()] = $percent;
            $totalDiscountPercent += $percent;
            if ($percent > 0) {
                $breakdown[] = "Desconto {$strategy->getName()}: {$percent}%";
            }
        }

        $quantityDiscount  = ($discountAmounts['quantity_discount'] ?? 0.0) / 100 * $priceWithMargin;
        $customerDiscount  = ($discountAmounts['customer_type_discount'] ?? 0.0) / 100 * $priceWithMargin;
        $premiumDiscount   = ($discountAmounts['premium_discount'] ?? 0.0) / 100 * $priceWithMargin;

        $totalDiscountAmount = $totalDiscountPercent / 100 * $priceWithMargin;
        $priceAfterDiscounts = $priceWithMargin - $totalDiscountAmount;

        // 3. Sobretaxa de frete (valor fixo por unidade para produto pesado)
        $freightSurcharge = $this->freightModifier->getSurcharge($dto);
        if ($freightSurcharge > 0) {
            $breakdown[] = "Sobretaxa frete (produto > 50kg): R$ " . number_format($freightSurcharge, 2, ',', '.');
        }

        $priceBeforeTax = $priceAfterDiscounts + $freightSurcharge;

        // 4. Impostos — calculado sobre o preço com desconto + frete
        $taxRate   = $this->taxStrategy->getRateForState($dto->state);
        $taxAmount = $priceBeforeTax * ($taxRate / 100);
        $breakdown[] = "ICMS {$dto->state} ({$taxRate}%): R$ " . number_format($taxAmount, 2, ',', '.');

        // 5. Preço unitário e total
        $unitPrice  = $priceBeforeTax + $taxAmount;
        $totalPrice = $unitPrice * $dto->quantity;

        $breakdown[] = "Preço unitário final: R$ " . number_format($unitPrice, 2, ',', '.');
        $breakdown[] = "Total ({$dto->quantity} un.): R$ " . number_format($totalPrice, 2, ',', '.');

        return new PriceCalculationResult(
            basePrice: $dto->basePrice,
            priceWithMargin: $priceWithMargin,
            quantityDiscount: $quantityDiscount,
            customerDiscount: $customerDiscount,
            premiumDiscount: $premiumDiscount,
            freightSurcharge: $freightSurcharge,
            taxAmount: $taxAmount,
            taxRate: $taxRate,
            unitPrice: $unitPrice,
            totalPrice: $totalPrice,
            quantity: $dto->quantity,
            breakdown: $breakdown,
        );
    }

    private function resultFromArray(array $data): PriceCalculationResult
    {
        return new PriceCalculationResult(
            basePrice: (float) $data['base_price'],
            priceWithMargin: (float) $data['price_with_margin'],
            quantityDiscount: (float) $data['discounts']['quantity'],
            customerDiscount: (float) $data['discounts']['customer'],
            premiumDiscount: (float) $data['discounts']['premium'],
            freightSurcharge: (float) $data['freight_surcharge'],
            taxAmount: (float) $data['tax']['amount'],
            taxRate: (float) $data['tax']['rate'],
            unitPrice: (float) $data['unit_price'],
            totalPrice: (float) $data['total_price'],
            quantity: (int) $data['quantity'],
            breakdown: $data['breakdown'] ?? [],
        );
    }
}
