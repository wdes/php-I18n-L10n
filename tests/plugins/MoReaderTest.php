<?php
declare(strict_types = 1);
/* The contents of this file is free and unencumbered software released into the
 * public domain.
 * For more information, please refer to <http://unlicense.org/>
 */
namespace Wdes\PIL\plugins;

use PHPUnit\Framework\TestCase;
use Wdes\PIL\plugins\MoReader;
use \stdClass;

/**
 * Test class for MoReader
 * @author William Desportes <williamdes@wdes.fr>
 * @license Unlicense
 */
class MoReaderTest extends TestCase
{
    public static $dir = __DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."data".DIRECTORY_SEPARATOR;

    /**
     * test Instance
     *
     * @return MoReader
     */
    public function testInstance(): MoReader
    {
        $moReader = new MoReader(
            array("localeDir" => self::$dir)
        );
        $this->assertInstanceOf(MoReader::class, $moReader);

        return $moReader;
    }

    /**
     * test Dir does not exist
     *
     * @expectedException     Exception
     * @expectedExceptionCode 0
     * @expectedExceptionMessageRegExp /The directory does not exist : (.+)/
     *
     * @return void
     */
    public function testException(): void
    {
        new MoReader(
            array("localeDir" => self::$dir.str_shuffle("abcdefghijklmnopqrstuv"))
        );
    }

    /**
     * test get Database
     * @depends testInstance
     * @param MoReader $moReader Config instance
     * @return void
     */
    public function testReadFile(MoReader $moReader): void
    {
        $data = $moReader->readFile(self::$dir."account-manager-en.mo");
        $this->assertInstanceOf(stdClass::class, $data);
        //echo \json_encode($data, JSON_PRETTY_PRINT);
        $data = $moReader->readFile(self::$dir."account-manager-fr.mo");
        $this->assertInstanceOf(stdClass::class, $data);
        //echo \json_encode($data, JSON_PRETTY_PRINT);
        $data = $moReader->readFile(self::$dir."fr_FR.mo");
        $this->assertInstanceOf(stdClass::class, $data);
        //echo \json_encode($data, JSON_PRETTY_PRINT);
        $data = $moReader->readFile(self::$dir."plurals1.mo");
        $this->assertInstanceOf(stdClass::class, $data);
        //echo \json_encode($data, JSON_PRETTY_PRINT);
    }

}
