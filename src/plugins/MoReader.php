<?php
declare(strict_types = 1);
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */
namespace Wdes\PIL\plugins;

use \Wdes\PIL\plugins\BasePlugin;
use \Exception;
use \stdClass;

/**
 * Plugin for reading .mo files
 * @see https://www.gnu.org/software/gettext/manual/html_node/MO-Files.html
 * @author William Desportes <williamdes@wdes.fr>
 * @license MPL-2.0
 */
class MoReader extends BasePlugin
{

    public const ENDIAN_BIG    = 1;
    public const ENDIAN_LITTLE = 2;

    /**
     * Data from mo file
     *
     * @var stdClass
     */
    private $data;

    /**
     * File resource
     *
     * @var resource
     */
    private $fileRes;

    /**
     * Build a MoReader instance
     *
     * @param array $options The options
     */
    public function __construct(array $options)
    {
        if (is_dir($options['localeDir']) == false) {
            throw new Exception("The directory does not exist : ".$options['localeDir']);
            return;
        }
    }

    /**
     * Read the .mo file
     *
     * @param string $file The complete path to the file
     * @return stdClass
     */
    public function readFile(string $file): stdClass
    {
        $this->data         = new stdClass();
        $this->fileRes      = fopen($file, 'rb');
        $this->data->endian = self::determineByteOrder($this->fileRes);
        if ($this->data->endian === -1) {
            fclose($this->fileRes);
            throw new Exception($file." is not a valid gettext file.");
        }

        fseek($this->fileRes, 4);
        $this->data->fileFormatRevision = self::readInteger($this->data->endian, $this->fileRes);
        if ($this->data->fileFormatRevision !== 0 && $this->data->fileFormatRevision !== 1) {
            fclose($this->fileRes);
            throw new Exception($file." has an unknown major revision.");
        }
        $this->data->nbrOfStrings           = self::readInteger($this->data->endian, $this->fileRes);
        $this->data->tableOffsetOriginal    = self::readInteger($this->data->endian, $this->fileRes);//offset of table with original strings
        $this->data->tableOffsetTranslation = self::readInteger($this->data->endian, $this->fileRes);//offset of table with translation strings

        // see: https://gist.github.com/timwhitlock/8255619
        // Hash table needs to be implemented if not useless
        //$this->data->tblHashSize            = self::readInteger($this->data->endian, $this->fileRes);//size of hashing table
        //$this->data->hashTableOffsetStart   = self::readInteger($this->data->endian, $this->fileRes);//offset of hashing table
        //$this->data->hashTableOffsetEnd     = $this->data->hashTableOffsetStart + $this->data->tblHashSize * 4;//offset of hashing table

        fseek($this->fileRes, $this->data->tableOffsetOriginal);
        $this->data->msgIdTable = self::readIntegerList($this->data->endian, $this->fileRes, 2 * $this->data->nbrOfStrings);
        fseek($this->fileRes, $this->data->tableOffsetTranslation);
        $this->data->msgStrTable = self::readIntegerList($this->data->endian, $this->fileRes, 2 * $this->data->nbrOfStrings);

        $this->data->translations = $this->readTranslations();

        // Clean memory
        unset($this->data->tableOffsetOriginal);
        unset($this->data->tableOffsetTranslation);
        unset($this->data->fileFormatRevision);
        unset($this->data->endian);
        unset($this->data->msgIdTable);
        unset($this->data->msgStrTable);

        // Close resource
        fclose($this->fileRes);
        return $this->data;
    }

    /**
     * Read translations
     *
     * @copyright 2015 Max Grigorian
     * @license MIT
     * @return array
     */
    public function readTranslations(): array
    {
        $data = array();
        for ($counter = 0; $counter < $this->data->nbrOfStrings; $counter++) {
            $msgId  = null;
            $msgStr = null;
            try {
                $msgId = $this->readStringFromTable($counter, $this->data->msgIdTable);
            } catch(Exception $e){
                $msgId = array('');
            }
            try {
                $msgStr = $this->readStringFromTable($counter, $this->data->msgStrTable);
            } catch(Exception $e){
                $msgStr = array();
            }
            $this->processRecord($data, $msgId, $msgStr);
        }
        return $data;
    }

    /**
     * Process a record
     *
     * @copyright 2015 Max Grigorian
     * @license MIT
     * @param array $data   The record
     * @param array $msgId  Message ids
     * @param array $msgStr Message strings
     * @return void
     */
    public function processRecord(array &$data, array $msgId, array $msgStr): void
    {
        // note: Contexts are stored by storing the concatenation of the context, a EOT byte, and the original string, instead of the original string.
        if (count($msgId) > 1 && count($msgStr) > 1) {
            $data[$msgId[0]] = $msgStr;
            array_shift($msgId);
            foreach ($msgId as $string) {
                $data[$string] = $msgStr;
            }
        } else {
            $data[$msgId[0]] = $msgStr[0];
        }
    }

    /**
     * Read a string from table
     *
     * @copyright 2015 Max Grigorian
     * @license MIT
     * @param int   $index Position
     * @param array $table Table
     *
     * @return array
     */
    public function readStringFromTable(int $index, array $table): array
    {
        $sizeKey = ($index * 2 ) + 1;
        $size    = $table[$sizeKey];
        if ($size > 0) {
            $offset = $table[$sizeKey + 1];
            fseek($this->fileRes, $offset);
            return explode("\0", fread($this->fileRes, $size));
        }
        throw new Exception("size error !");
    }

    /**
     * Determines byte order
     *
     * @copyright 2015 Max Grigorian
     * @license MIT
     * @see github.com/MAXakaWIZARD/MoReader:src/Reader.php
     * @param resource $res The file resource
     * @return int ENDIAN_BIG/ENDIAN_LITTLE/-1
     */
    public static function determineByteOrder($res): int
    {
        $orderBytes = fread($res, 4);
        if ($orderBytes === "\x95\x04\x12\xde") {
            return self::ENDIAN_BIG;
        } elseif ($orderBytes === "\xde\x12\x04\x95") {
            return self::ENDIAN_LITTLE;
        } else {
            return -1;
        }
    }

    /**
     * Read an integer from the current file.
     *
     * @copyright 2015 Max Grigorian
     * @license MIT
     * @see github.com/MAXakaWIZARD/MoReader:src/Reader.php
     * @param  int      $endian ENDIAN_BIG/ENDIAN_LITTLE
     * @param  resource $res    The file resource
     * @param  int      $nbr    Number of strings
     * @return array
     */
    public static function readIntegerList(int $endian, $res, int $nbr): array
    {
        if ($endian === self::ENDIAN_LITTLE) {
            return unpack('V' . $nbr, fread($res, 4 * $nbr));
        } else {
            return unpack('N' . $nbr, fread($res, 4 * $nbr));
        }
    }

    /**
     * Read a single integer from the current file.
     *
     * @copyright 2015 Max Grigorian
     * @license MIT
     * @param int      $endian ENDIAN_BIG/ENDIAN_LITTLE
     * @param  resource $res    The file resource
     * @return integer
     */
    public static function readInteger(int $endian, $res)
    {
        if ($endian === self::ENDIAN_LITTLE) {
            $result = unpack('Vint', fread($res, 4));
        } else {
            $result = unpack('Nint', fread($res, 4));
        }
        return $result['int'];
    }

    /**
     * Return string id or translation
     *
     * @param mixed $msgId The message id
     * @return string
     */
    public function idOrFind($msgId): string
    {
        if (array_key_exists($msgId, $this->data->translations)) {
            return $this->data->translations[$msgId];
        } else {
            return $msgId;
        }
    }

    /**
     * Get translation for a message id
     *
     * @param mixed $msgId Message id
     * @return string
     */
    public function __($msgId): string
    {
        return self::gettext($msgId);
    }

    /**
     * Get translation for a message id
     *
     * @param mixed $msgId Message id
     * @return string
     */
    public function gettext($msgId): string
    {
        return self::idOrFind($msgId);
    }

    /**
         * Get translation
         *
         * @param mixed $domain      Domain
         * @param mixed $msgctxt     msgctxt
         * @param mixed $msgId       Message id
         * @param mixed $msgIdPlural Plural message id
         * @param mixed $number      Number
         * @return string
         */
    public function dnpgettext($domain, $msgctxt, $msgId, $msgIdPlural, $number): string
    {
        return self::idOrFind($msgId);
    }

    /**
         * Get translation
         *
         * @param mixed $domain      Domain
         * @param mixed $msgId       Message id
         * @param mixed $msgIdPlural Plural message id
         * @param mixed $number      Number
         * @return string
         */
    public function dngettext($domain, $msgId, $msgIdPlural, $number): string
    {
        return self::idOrFind($msgId);
    }

    /**
         * Get translation
         *
         * @param mixed $msgctxt     msgctxt
         * @param mixed $msgId       Message id
         * @param mixed $msgIdPlural Plural message id
         * @param mixed $number      Number
         * @return string
         */
    public function npgettext($msgctxt, $msgId, $msgIdPlural, $number): string
    {
        return self::idOrFind($msgId);
    }

    /**
         * Get translation
         *
         * @param mixed $msgId       Message id
         * @param mixed $msgIdPlural Plural message id
         * @param mixed $number      Number
         * @return string
         */
    public function ngettext($msgId, $msgIdPlural, $number): string
    {
        return self::idOrFind($msgId);
    }

    /**
         * Get translation
         *
         * @param mixed $domain  Domain
         * @param mixed $msgctxt msgctxt
         * @param mixed $msgId   Message id
         * @return string
         */
    public function dpgettext($domain, $msgctxt, $msgId): string
    {
        return self::idOrFind($msgId);
    }

    /**
         * Get translation
         *
         * @param mixed $domain Domain
         * @param mixed $msgId  Message id
         * @return string
         */
    public function dgettext($domain, $msgId): string
    {
        return self::idOrFind($msgId);
    }

    /**
         * Get translation
         *
         * @param mixed $msgctxt msgctxt
         * @param mixed $msgId   Message id
         * @return string
         */
    public function pgettext($msgctxt, $msgId): string
    {
        return self::idOrFind($msgId);
    }

}
