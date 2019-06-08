<?php
declare(strict_types = 1);
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */
namespace Wdes\PIL\Twig\Extension;

use \Wdes\PIL\Twig\TokenParser;
use \Twig\TwigFilter;
use \Twig\Extensions\I18nExtension;

/**
 * I18n extension for Twig
 * @license MPL-2.0
 */
class I18n extends I18nExtension
{

    /**
     * Get the filters
     *
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'trans', '\Wdes\PIL\Launcher::gettext'
            ),
        ];
    }

    /**
     * Get the token parsers
     *
     * @return \Twig\TokenParser\TokenParserInterface[]
     */
    public function getTokenParsers(): array
    {
        return [
            new TokenParser()
        ];
    }

}
