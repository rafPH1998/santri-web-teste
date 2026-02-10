<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\DTOs\PriceCalculationDTO;
use App\Exceptions\InvalidCalculationDataException;
use App\Http\Controllers\Controller;
use App\Services\Pricing\ProductCalculatorFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class PriceCalculateController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $dto = PriceCalculationDTO::fromArray($request->all());
        } catch (InvalidCalculationDataException $e) {
            return response()->json([
                'error'   => 'Dados inválidos',
                'message' => $e->getMessage(),
            ], 422);
        }

        try {
            $calculator = ProductCalculatorFactory::createDefault();
            $result     = $calculator->calculate($dto);

            return response()->json([
                'success' => true,
                'data'    => $result->toArray(),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Erro interno',
                'message' => 'Não foi possível calcular o preço. Tente novamente.',
            ], 500);
        }
    }
}