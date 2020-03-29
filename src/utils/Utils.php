<?php
declare(strict_types = 1);
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */
namespace Wdes\PIL\utils;

/**
 * Utils
 */
class Utils
{

    /**
         * Displays hash table as hex
         *
         * @param string $str The string
         * @return void
         */
    public static function print_binary( $str ): void
    {
        $pairs = str_split(bin2hex($str), 2);
        $lines = str_split(implode(' ', $pairs), 12);
        echo strlen($str)," bytes\n",implode("\n", $lines), "\n\n";
    }

    /**
     * @see https://gist.github.com/timwhitlock/8255619
     * @see https://wiki.dreamrunner.org/public_html/Algorithms/TheoryOfAlgorithms/HashTable.html
     * hashpjw function by P.J. Weinberger. (From hash-string.c)
     * http://fossies.org/dox/gettext-0.18.3.1/hash-string_8c_source.html#l00031
     * @param string $str The string to hash
     * @return int
     */
    public static function ELFHash(string $str): int
    {
        $i   = -1;
        $h   = 0;
        $len = strlen($str);
        while (++$i < $len) {
            $ord = ord($str[$i]);
            $h   = ( $h << 4 ) + $ord;
            $g   = $h & 0xf0000000;
            if ($g !== 0) {
                $h ^= $g >> 24;
                $h ^= $g;
            }
        }
        return $h;
    }

    /**
     * @see https://gist.github.com/timwhitlock/8255619
     * Get next prime number up from seed number. (From hash.c)
     * http://fossies.org/dox/gettext-0.18.3.1/gettext-tools_2gnulib-lib_2hash_8c_source.html#l00073
     *
     * @param int $seed The seed
     * @return int
     */
    public static function next_prime( $seed )
    {
        $seed |= 1;
        while (! self::isPrime($seed)) {
            $seed += 2;
        }
        return $seed;
    }

    /**
     * @see https://gist.github.com/timwhitlock/8255619
     * Test if a given integer is a prime number.
     * http://icdif.com/computing/2011/09/15/check-number-prime-number/
     * @param int $num The number
     * @return bool
     */
    public static function isPrime( int $num )
    {
        if ($num === 1) {
            return false;
        }
        if ($num === 2) {
            return true;
        }
        if ($num % 2 === 0) {
            return false;
        }
        $max = ceil(sqrt($num));
        for ($i = 3; $i <= $max; $i += 2) {
            if ($num % $i === 0) {
                return false;
            }
        }
        return true;
    }

}
