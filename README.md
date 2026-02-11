# E-commerce Pricing Calculator

Biblioteca de cálculo de preços para e-commerce B2B de material de construção, desenvolvida em PHP 8.4, Laravel 12, Docker e Docker Compose.
# OBS: (Importante ressaltar que você deve ter em sua máquina instalada o docker para rodar e testar o projeto)

## Funcionalidades

- Cálculo de preço com margem de lucro configurável
- Descontos progressivos por quantidade
- Descontos por tipo de cliente (varejo, atacado, revendedor)
- Desconto adicional para clientes Premium
- Sobretaxa de frete para produtos pesados (>50kg)
- ICMS configurável por estado
- Cache de cálculos para performance
- API REST para integração

## Regras de Negócio

| Faixa de Quantidade | Desconto |
|---------------------|----------|
| 1 – 9 unidades      | 0%       |
| 10 – 49 unidades    | 3%       |
| 50+ unidades        | 5%       |

| Tipo de Cliente | Desconto |
|-----------------|----------|
| Varejo          | 0%       |
| Atacado         | 5%       |
| Revendedor      | 8%       |

- Cliente **Premium**: +2% de desconto adicional
- Produtos **> 50kg**: sobretaxa de R$ 15,00 por unidade
- ICMS varia por estado: SP (18%), RJ (20%), RS (17%), etc.

> Os descontos são somados (não compostos). Ex: atacado + premium = 10% total.

## Estrutura dos Arquivos (aplicagem dos patters)

```
app/
├── DTOs/
│   ├── PriceCalculationDTO.php        # Dados de entrada validados
│   └── PriceCalculationResult.php     # Resultado do cálculo
├── Exceptions/
│   └── InvalidCalculationDataException.php
├── Http/Controllers/Api/
│   └── PriceCalculateController.php   # POST /api/calculate
└── Services/Pricing/
    ├── ProductCalculator.php           # Classe principal
    ├── ProductCalculatorFactory.php    # Factory com configuração padrão
    ├── Cache/
    │   ├── PriceCacheInterface.php
    │   └── LaravelCacheAdapter.php     # Usa o cache do Laravel (Redis/file/etc)
    ├── Modifiers/
    │   └── FreightModifier.php         # Sobretaxa de frete
    └── Strategies/
        ├── DiscountStrategyInterface.php
        ├── TaxStrategyInterface.php
        ├── QuantityDiscountStrategy.php
        ├── CustomerTypeDiscountStrategy.php
        ├── PremiumDiscountStrategy.php
        └── IcmsTaxStrategy.php
tests/Unit/Services/Pricing/
    ├── ProductCalculatorTest.php
    ├── PriceCalculationDTOTest.php
    ├── QuantityDiscountStrategyTest.php
    ├── CustomerTypeDiscountStrategyTest.php
    ├── PremiumDiscountStrategyTest.php
    ├── FreightModifierTest.php
    └── IcmsTaxStrategyTest.php
```
## Instalação do projeto

## Clone Repositório
```
git clone git@github.com:rafPH1998/santri-web-teste.git
cd santri-web-teste
```

## Crie o Arquivo .env

```
cp .env.example .env
```

## Suba os containers do projeto

```
docker compose up -d
```

## Entre dentro do container

```
 docker compose exec app bash
```

## Dentro do container, rode o comando abaixo para gerar as dependencias do projeto

```
composer install
```

## Gere a key do projeto ainda dentro do container

```
php artisan key:generate
```

# Caso gere erro ao rodar composer install, é importante saber o id que se encontra o seu usuário

![alt text](image.png)

Verifique se o ID esta igual ao do arquivo Dockerfile, caso não estiver, deixe o ID do dockerfile similar ao do usuario. Rode docker compose up --build para subir os container de novo e tente o processo novamente.

![alt text](image-1.png)

----------------------------------------------------------------------------------------------------------------------
## Testes

### Ainda dentro do container, rode o comando abaixo para rodar os testes da aplicação
```bash
php artisan test --testsuite=Unit --filter=Pricing
```

## Usando o Calculator direto (sem API)

```php
use App\DTOs\PriceCalculationDTO;
use App\Services\Pricing\ProductCalculatorFactory;

$dto = new PriceCalculationDTO(
    basePrice: 100.0,
    quantity: 50,
    customerType: 'atacado',
    state: 'SP',
    weightKg: 10.0,
    isPremium: true,
    profitMargin: 15.0,
);

$calculator = ProductCalculatorFactory::createDefault();
$result = $calculator->calculate($dto);

echo $result->totalPrice; // preço total calculado
print_r($result->toArray()); // todos os detalhes
```


## Uso da API

**Endpoint:** `POST /api/calculate`

**Payload:**
```json
{
    "base_price": 250.00,
    "quantity": 50,
    "customer_type": "atacado",
    "state": "SP",
    "weight_kg": 55.0,
    "is_premium": true,
    "profit_margin": 15.0
}
```

**Resposta:**
```json
{
    "success": true,
    "data": {
        "base_price": 250.00,
        "price_with_margin": 287.50,
        "discounts": {
            "quantity": 14.38,
            "customer": 14.38,
            "premium": 5.75
        },
        "freight_surcharge": 15.00,
        "tax": {
            "rate": 18.0,
            "amount": 46.82
        },
        "unit_price": 314.31,
        "quantity": 50,
        "total_price": 15715.50,
        "breakdown": [...]
    }
}
```

**Tipos de cliente aceitos:** `varejo`, `atacado`, `revendedor`

**Estados:** sigla em maiúsculo (SP, RJ, MG, RS, PR...)

