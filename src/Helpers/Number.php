<?php

namespace App\Helpers;

class Number
{
    /**
     * Verifica si un número es potencia de 2.
     *
     * @param int $n Número a evaluar.
     * @return bool Verdadero si es una potencia de 2, falso en caso contrario.
     */
    public static function isPowerOfTwo(int $n): bool {
        return ($n > 0) && (($n & ($n - 1)) === 0);
    }
}
