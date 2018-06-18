<?php
declare(strict_types = 1);
/* The contents of this file is free and unencumbered software released into the
 * public domain.
 * For more information, please refer to <http://unlicense.org/>
 */
namespace Wdes\PIL\utils;

use \PHPUnit\Framework\TestCase;
use \Wdes\PIL\utils\Utils;

/**
 * Test class for Utils
 * @author William Desportes <williamdes@wdes.fr>
 * @license Unlicense
 */
class UtilsTest extends TestCase
{

    /**
     * Provides data to test hashString
     * Created from test data found on the Internet
     *
     * @return array<int, array<int, int|string>>
     */
    public function ELFHashProvider(): array
    {
        return array(
            array("0",48),
            array("ABCDEFGHIJKLMN", 178683902),
            array("Dashboard", 166269444),
            array("ABCDEFGHIJKLMNABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz", 163039834),
            array("abcdefghijklmnopqrstuvwxyz1234567890", 126631744),
            array("jdfgsdhfsdfsd 6445dsfsd7fg/*/+bfjsdgf%$^", 248446350)
        );
    }

    /**
     * test hash string
     * @dataProvider ELFHashProvider
     *
     * @param string $string The string
     * @param int    $expect The expected result
     * @return void
     */
    public function testELFHash(string $string, int $expect): void
    {
        $this->assertEquals($expect, Utils::ELFHash($string));
    }

}
