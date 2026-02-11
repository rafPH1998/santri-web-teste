<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Exception lançada quando os dados de entrada do cálculo são inválidos.
 */
class InvalidCalculationDataException extends RuntimeException {}
