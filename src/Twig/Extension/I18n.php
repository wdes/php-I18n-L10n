<?php
declare(strict_types = 1);
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */
namespace Wdes\phpI18nL10n\Twig\Extension;

use PhpMyAdmin\Twig\Extensions\I18nExtension;
use Wdes\phpI18nL10n\Launcher;
use Wdes\phpI18nL10n\Twig\TokenParser;

/**
 * I18n extension for Twig
 * @license MPL-2.0
 */
class I18n extends I18nExtension
{

    /**
     * @return array
     */
    public function getTokenParsers()
    {
        return [new TokenParser()];
    }

    /**
     * Translate a GetText string via filter
     *
     * @param string      $message The message to translate
     * @param string|null $domain  The GetText domain
     *
     * @return string The translated string
     */
    public function translate(string $message, ?string $domain = null): string
    {
        /* If we don't have a domain, assume we're just using the default */
        if ($domain === null) {
            return Launcher::gettext($message);
        }

        /* Otherwise specify where the message comes from */
        return Launcher::dgettext($domain, $message);
    }

}
