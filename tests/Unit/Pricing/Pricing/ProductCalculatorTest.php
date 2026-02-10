<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Pricing;

use App\DTOs\PriceCalculationDTO;
use App\DTOs\PriceCalculationResult;
use App\Services\Pricing\Cache\PriceCacheInterface;
use App\Services\Pricing\Modifiers\FreightModifier;
use App\Services\Pricing\ProductCalculator;
use App\Services\Pricing\Strategies\CustomerTypeDiscountStrategy;
use App\Services\Pricing\Strategies\IcmsTaxStrategy;
use App\Services\Pricing\Strategies\PremiumDiscountStrategy;
use App\Services\Pricing\Strategies\QuantityDiscountStrategy;
use PHPUnit\Framework\TestCase;

class ProductCalculatorTest extends TestCase
{
    private ProductCalculator $calculator;
    private PriceCacheInterface $cache;

    protected function setUp(): void
    {
        // Cache que nunca retorna nada (forçar cálculo real nos testes)
        $this->cache = new class implements PriceCacheInterface {
            public function get(string $key): ?array { return null; }
            public function set(string $key, array $data, int $ttlSeconds = 300): void {}
            public function forget(string $key): void {}
        };

        $this->calculator = new ProductCalculator(
            taxStrategy: new IcmsTaxStrategy(),
            freightModifier: new FreightModifier(),
            cache: $this->cache,
        );

        $this->calculator
            ->addDiscountStrategy(new QuantityDiscountStrategy())
            ->addDiscountStrategy(new CustomerTypeDiscountStrategy())
            ->addDiscountStrategy(new PremiumDiscountStrategy());
    }

    public function test_basic_calculation_no_discounts(): void
    {
        // Produto simples: 1 unidade, cliente varejo, SP, sem premium, sem margem
        // R$ 100 * (1 + 18%) = R$ 118,00
        $dto = new PriceCalculationDTO(
            basePrice: 100.0,
            quantity: 1,
            customerType: 'varejo',
            state: 'SP',
            weightKg: 5.0,
        );

        $result = $this->calculator->calculate($dto);

        $this->assertInstanceOf(PriceCalculationResult::class, $result);
        $this->assertEquals(100.0, $result->basePrice);
        $this->assertEquals(18.0, $result->taxRate);
        $this->assertEquals(18.0, $result->taxAmount);
        $this->assertEquals(118.0, $result->unitPrice);
        $this->assertEquals(118.0, $result->totalPrice);
    }

    public function test_quantity_discount_10_units(): void
    {
        // 10 unidades = 3% de desconto
        // Preço: 100 - 3% = 97 + 18% ICMS = 114,46
        $dto = new PriceCalculationDTO(
            basePrice: 100.0,
            quantity: 10,
            customerType: 'varejo',
            state: 'SP',
            weightKg: 5.0,
        );

        $result = $this->calculator->calculate($dto);

        $this->assertEquals(97.0, $result->priceWithMargin - $result->quantityDiscount);
        $this->assertEquals(10, $result->quantity);

        // Total = 10 * unitPrice
        $this->assertEqualsWithDelta(1144.60, $result->totalPrice, 0.01);
    }

    public function test_heavy_product_surcharge(): void
    {
        // Produto > 50kg tem sobretaxa de R$ 15,00
        $dto = new PriceCalculationDTO(
            basePrice: 100.0,
            quantity: 1,
            customerType: 'varejo',
            state: 'SP',
            weightKg: 60.0,
        );

        $result = $this->calculator->calculate($dto);

        $this->assertEquals(15.0, $result->freightSurcharge);
        // (100 + 15) * 1.18 = 135.70
        $this->assertEqualsWithDelta(135.70, $result->unitPrice, 0.01);
    }

    public function test_rj_tax_rate(): void
    {
        $dto = new PriceCalculationDTO(
            basePrice: 100.0,
            quantity: 1,
            customerType: 'varejo',
            state: 'RJ',
            weightKg: 5.0,
        );

        $result = $this->calculator->calculate($dto);

        $this->assertEquals(20.0, $result->taxRate);
        $this->assertEquals(20.0, $result->taxAmount);
        $this->assertEquals(120.0, $result->unitPrice);
    }

    public function test_premium_customer_extra_discount(): void
    {
        $dto = new PriceCalculationDTO(
            basePrice: 100.0,
            quantity: 1,
            customerType: 'varejo',
            state: 'SP',
            weightKg: 5.0,
            isPremium: true,
        );

        $result = $this->calculator->calculate($dto);

        // 2% de desconto premium
        $this->assertEquals(2.0, $result->premiumDiscount);
    }

    public function test_profit_margin_applied(): void
    {
        // Margem de 10% sobre preço base de 100 = 110
        $dto = new PriceCalculationDTO(
            basePrice: 100.0,
            quantity: 1,
            customerType: 'varejo',
            state: 'SP',
            weightKg: 5.0,
            profitMargin: 10.0,
        );

        $result = $this->calculator->calculate($dto);

        $this->assertEqualsWithDelta(110.0, $result->priceWithMargin, 0.01);
    }

    public function test_cache_is_used_on_second_call(): void
    {
        $callCount = 0;

        $cacheSpy = new class($callCount) implements PriceCacheInterface {
            public int $setCount = 0;
            public int $getCount = 0;
            private ?array $stored = null;

            public function get(string $key): ?array
            {
                $this->getCount++;
                return $this->stored;
            }

            public function set(string $key, array $data, int $ttlSeconds = 300): void
            {
                $this->setCount++;
                $this->stored = $data;
            }

            public function forget(string $key): void {}
        };

        $calculator = new ProductCalculator(
            taxStrategy: new IcmsTaxStrategy(),
            freightModifier: new FreightModifier(),
            cache: $cacheSpy,
        );

        $dto = new PriceCalculationDTO(
            basePrice: 100.0,
            quantity: 1,
            customerType: 'varejo',
            state: 'SP',
            weightKg: 5.0,
        );

        $calculator->calculate($dto);
        $calculator->calculate($dto); // segunda vez deve vir do cache

        $this->assertEquals(1, $cacheSpy->setCount);
        $this->assertEquals(2, $cacheSpy->getCount);
    }

    public function test_combined_discounts(): void
    {
        // atacado (5%) + 50 unidades (5%) + premium (2%) = 12% total
        $dto = new PriceCalculationDTO(
            basePrice: 100.0,
            quantity: 50,
            customerType: 'atacado',
            state: 'SP',
            weightKg: 5.0,
            isPremium: true,
        );

        $result = $this->calculator->calculate($dto);

        // Preço após descontos: 100 * (1 - 0.12) = 88
        // Imposto SP: 88 * 0.18 = 15.84
        // Unitário: 88 + 15.84 = 103.84
        // Total: 103.84 * 50 = 5192.00
        $this->assertEqualsWithDelta(103.84, $result->unitPrice, 0.01);
        $this->assertEqualsWithDelta(5192.0, $result->totalPrice, 0.01);
    }

    public function test_result_has_breakdown(): void
    {
        $dto = new PriceCalculationDTO(
            basePrice: 100.0,
            quantity: 1,
            customerType: 'varejo',
            state: 'SP',
            weightKg: 5.0,
        );

        $result = $this->calculator->calculate($dto);

        $this->assertNotEmpty($result->breakdown);
        $this->assertIsArray($result->breakdown);
    }
}