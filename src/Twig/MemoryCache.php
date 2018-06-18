<?php
declare(strict_types = 1);
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */
namespace Wdes\PIL\Twig;

/**
 * Token parser for Twig
 * @license MPL-2.0
 */
class MemoryCache implements \Twig_CacheInterface
{
    /**
     * Contains all templates
     *
     * @var array<string, string>
     */
    private $memory = array();

    /**
     * Generate a cache key
     *
     * @param string $name      The name
     * @param string $className The class name
     * @return string
     */
    public function generateKey($name, $className): string
    {
        return "memcache_".$name;
    }

    /**
     * Write to cache
     *
     * @param string $key     The cache key
     * @param string $content The content to write
     * @return void
     */
    public function write($key, $content): void
    {
        $this->memory[$key] = $content;
    }

    /**
     * Load from cache
     *
     * @param string $key The cache key
     * @return void
     */
    public function load($key)
    {

    }

    /**
     * Get the timestamp
     *
     * @param string $key The cache key
     * @return int
     */
    public function getTimestamp($key): int
    {
        return 0;
    }

    /**
     * Get the cached string for key
     *
     * @param \Twig_Template $template The template
     * @return string
     */
    public function getFromCache(\Twig_Template $template): string
    {
        return $this->memory["memcache_".$template->getTemplateName()];
    }

    /**
     * Extract source code from memory cache
     *
     * @param \Twig_Template $template The template
     * @return string
     */
    public function extractDoDisplayFromCache(\Twig_Template $template): string
    {
        $content = self::getFromCache($template);
        // "/function ([a-z_]+)\("
        preg_match_all(
            '/protected function doDisplay\(([a-z\s,\$=\(\)]+)\)([\s]+){([=%\sa-z\/0-9-\>:\(\)\\\";.,\$\[\]?_-]+)/msi', $content, $output_array
        );
        //echo $content;
        return $output_array[3][0];
    }

}
