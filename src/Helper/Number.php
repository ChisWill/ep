<?php

declare(strict_types=1);

namespace Ep\Helper;

class Number
{
    /**
     * 判断是否是质数
     * 
     * @param  int $number
     * 
     * @return bool
     */
    public static function isPrime(int $number): bool
    {
        if ($number < 2) {
            return false;
        }
        if ($number == 2 || $number == 3) {
            return true;
        }
        if ($number % 6 != 1 && $number % 6 != 5) {
            return false;
        }
        $sqrt = sqrt($number);
        for ($i = 5; $i <= $sqrt; $i += 6) {
            if ($number % $i == 0 || $number % ($i + 2) == 0) {
                return false;
            }
        }
        return true;
    }
}
