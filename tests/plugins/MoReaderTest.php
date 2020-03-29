<?php
declare(strict_types = 1);
/* The contents of this file is free and unencumbered software released into the
 * public domain.
 * For more information, please refer to <http://unlicense.org/>
 */
namespace Wdes\PIL\plugins;

use Exception;
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
    /**
     * @var string
     */
    public static $dir = __DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."data".DIRECTORY_SEPARATOR;

    /**
     * test Instance
     *
     * @return MoReader
     */
    public function testInstance(): MoReader
    {
        $moReader = new MoReader(
            ["localeDir" => self::$dir]
        );
        $this->assertInstanceOf(MoReader::class, $moReader);

        return $moReader;
    }

    /**
     * test Dir does not exist
     *
     * @return void
     */
    public function testException(): void
    {
        $folderString = self::$dir . str_shuffle("abcdefghijklmnopqrstuv");
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The directory does not exist : ' . $folderString);
        $this->expectExceptionCode(0);
        new MoReader(
            ['localeDir' => $folderString]
        );
    }

    /**
     * test read file
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

    /**
     * test read invalid file
     * @depends testInstance
     * @param MoReader $moReader Config instance
     * @return void
     */
    public function testReadInvalidFile(MoReader $moReader): void
    {
        $fileName = self::$dir . 'account-manager-ru.mo';
        $this->expectException(Exception::class);
        $this->expectExceptionMessage($fileName . ' does not exist.');
        $this->expectExceptionCode(0);
        $moReader->readFile($fileName);
    }

    /**
     * test translate
     * @depends testInstance
     * @param MoReader $moReader Config instance
     * @return void
     */
    public function testTranslate(MoReader $moReader): void
    {
        $data = $moReader->readFile(self::$dir . 'abc.mo');
        $this->assertInstanceOf(stdClass::class, $data);
        $this->assertSame('Traduis ça', $moReader->__('Translate this'));
        $this->assertSame('Traduis ça', $moReader->dnpgettext('', '', 'Translate this', '', 0));
        $this->assertSame('Traduis ça', $moReader->ngettext('Translate this', 'Translate this', 1));
        $this->assertSame('Traduis ça', $moReader->ngettext('Translate this', 'Translate this', 2));
        $this->assertSame('Traduis ça', $moReader->dgettext('', 'Translate this'));
    }
}
