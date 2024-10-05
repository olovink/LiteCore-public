<?php

declare(strict_types=1);

namespace pocketmine\math;

use pocketmine\utils\Random;

class MathHelper {
    private static array $a = [];

    public static function sqrt($paramFloat): float{
        return sqrt($paramFloat);
    }

    public static function sin($paramFloat) {
        return self::$a[(int) ($paramFloat * 10430.378) & 0xFFFF];
    }

    public static function cos($paramFloat) {
        return self::$a[(int) ($paramFloat * 10430.378 + 16384.0) & 0xFFFF];
    }

    public static function floor($d0): int{
        $i = (int) $d0;
        return $d0 < $i ? $i - 1 : $i;
    }

    public static function floor_double_long($d): int{
        $l = (int) $d;
        return $d >= $l ? $l : $l - 1;
    }

    public static function abs($number) {
        return $number > 0 ? $number : -$number;
    }

    public static function log2nlz($bits): int{
        if ($bits == 0) {
            return 0; // or throw exception
        }
        return 31 - intval(log($bits) / log(2));
    }

    /**
     * Returns a random number between min and max, inclusive.
     *
     * @param Random $random The random number generator.
     * @param int $min The minimum value.
     * @param int $max The maximum value.
     * @return int A random number between min and max, inclusive.
     */
    public static function getRandomNumberInRange(Random $random, int $min, int $max): int{
        return $min + rand($min, $max);
    }

    public static function initialize(): void{
        for ($i = 0; $i < 65536; $i++) {
            self::$a[$i] = sin($i * 3.141592653589793 * 2.0 / 65536.0);
        }
    }

    public static function max($first, $second, $third, $fourth) {
        if ($first > $second && $first > $third && $first > $fourth) {
            return $first;
        }
        if ($second > $third && $second > $fourth) {
            return $second;
        }
        if ($third > $fourth) {
            return $third;
        }
        return $fourth;
    }

    public static function ceil($floatNumber): int{
        $truncated = (int) $floatNumber;
        return $floatNumber > $truncated ? $truncated + 1 : $truncated;
    }

    public static function clamp($check, $min, $max) {
        return $check > $max ? $max : (max($check, $min));
    }

}