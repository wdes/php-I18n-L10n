<?php
declare(strict_types = 1);
/* The contents of this file is free and unencumbered software released into the
 * public domain.
 * For more information, please refer to <http://unlicense.org/>
 */
namespace Wdes\phpI18nL10n\Tests;

use \PHPUnit\Framework\TestCase;
use Twig\Node\Node;
use Twig\Compiler;
use Twig\Node\Expression\NameExpression;
use \Twig\Environment as TwigEnv;
use \Twig\Loader\FilesystemLoader as TwigLoaderFilesystem;
use Wdes\phpI18nL10n\Twig\TranslationNode;
use Wdes\phpI18nL10n\Twig\MemoryCache;

/**
 * @author William Desportes <williamdes@wdes.fr>
 * @license Unlicense
 */
class TranslationNodeTest extends TestCase
{

    /**
     * Get a compiler to make tests
     * @return Compiler
     */
    public function getCompiler(): Compiler
    {
        $loader      = new TwigLoaderFilesystem();
        $memoryCache = new MemoryCache();
        $twig        = new TwigEnv(
            $loader, [
                'cache' => $memoryCache,
                'debug' => true
            ]
        );
        return new Compiler($twig);
    }

    /**
     * test for Launcher::gettext
     *
     * @return void
     */
    public function testGetText(): void
    {
        $tn       = new TranslationNode(
            new Node([]),
            null,
            null,
            null,
            null,
            0,
            null
        );
        $compiler = $this->getCompiler();
        $compiler->raw('[');
        $vars = [
            new NameExpression('d', 0),
            new NameExpression('f', 0),
            new NameExpression('g', 0),
        ];
        $tn->compileVarsArgs($compiler, $vars);
        $compiler->raw(']');
        $this->assertSame('["%d%" => ($context["d"] ?? null), "%f%" => ($context["f"] ?? null), "%g%" => ($context["g"] ?? null)]', $compiler->getSource());
    }

    /**
     * test for Launcher::gettext
     *
     * @return void
     */
    public function testGetTextOneVariable(): void
    {
        $tn       = new TranslationNode(
            new Node([]),
            null,
            null,
            null,
            null,
            0,
            null
        );
        $compiler = $this->getCompiler();
        $compiler->raw('[');
        $vars = [
            new NameExpression('d', 0),
        ];
        $tn->compileVarsArgs($compiler, $vars);
        $compiler->raw(']');
        $this->assertSame('["%d%" => ($context["d"] ?? null)]', $compiler->getSource());
    }

}
