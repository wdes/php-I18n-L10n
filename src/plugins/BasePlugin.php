<?php
declare(strict_types = 1);
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */
namespace Wdes\PIL\plugins;

/**
 * Plugin interface
 * @author William Desportes <williamdes@wdes.fr>
 * @license MPL-2.0
 */
abstract class BasePlugin
{

    /**
     * Build the plugin instance
     *
     * @param array $options The options for the plugin
     */
    abstract public function __construct(array $options);

    /**
     * Get translation for a message id
     *
     * @param string $msgId Message id
     * @return string
     */
    abstract public function gettext(string $msgId): string;

    /**
     * Get translation for a message id
     *
     * @param mixed $msgId Message id
     * @return string
     */
    abstract public function __($msgId): string;

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
    abstract public function dnpgettext($domain, $msgctxt, $msgId, $msgIdPlural, $number): string;

    /**
     * Get translation
     *
     * @param mixed $domain      Domain
     * @param mixed $msgId       Message id
     * @param mixed $msgIdPlural Plural message id
     * @param mixed $number      Number
     * @return string
     */
    abstract public function dngettext($domain, $msgId, $msgIdPlural, $number): string;

    /**
     * Get translation
     *
     * @param mixed $msgctxt     msgctxt
     * @param mixed $msgId       Message id
     * @param mixed $msgIdPlural Plural message id
     * @param mixed $number      Number
     * @return string
     */
    abstract public function npgettext($msgctxt, $msgId, $msgIdPlural, $number): string;

    /**
     * Plural version of gettext
     *
     * @param string $msgId       Message id
     * @param string $msgIdPlural Plural message id
     * @param int    $number      Number
     * @return string
     */
    abstract public function ngettext(string $msgId, string $msgIdPlural, int $number);

    /**
     * Get translation
     *
     * @param mixed $domain  Domain
     * @param mixed $msgctxt msgctxt
     * @param mixed $msgId   Message id
     * @return string
     */
    abstract public function dpgettext($domain, $msgctxt, $msgId): string;

    /**
     * Get translation
     *
     * @param mixed $domain Domain
     * @param mixed $msgId  Message id
     * @return string
     */
    abstract public function dgettext($domain, $msgId): string;

    /**
     * Get translation
     *
     * @param mixed $msgctxt msgctxt
     * @param mixed $msgId   Message id
     * @return string
     */
    abstract public function pgettext($msgctxt, $msgId): string;

}
