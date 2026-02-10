<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Pricing;

use App\Services\Pricing\Strategies\IcmsTaxStrategy;
use PHPUnit\Framework\TestCase;

class IcmsTaxStrategyTest extends TestCase
{
    private IcmsTaxStrategy $strategy;

    protected function setUp(): void
    {
        $this->strategy = new IcmsTaxStrategy();
    }

    public function test_sp_rate(): void
    {
        $this->assertEquals(18.0, $this->strategy->getRateForState('SP'));
    }

    public function test_rj_rate(): void
    {
        $this->assertEquals(20.0, $this->strategy->getRateForState('RJ'));
    }

    public function test_case_insensitive(): void
    {
        $this->assertEquals(18.0, $this->strategy->getRateForState('sp'));
        $this->assertEquals(20.0, $this->strategy->getRateForState('rj'));
    }

    public function test_unknown_state_returns_default(): void
    {
        // Estado que não existe -> usa o padrão 18%
        $rate = $this->strategy->getRateForState('XX');
        $this->assertEquals(18.0, $rate);
    }

    public function test_rs_rate(): void
    {
        $this->assertEquals(17.0, $this->strategy->getRateForState('RS'));
    }
}
