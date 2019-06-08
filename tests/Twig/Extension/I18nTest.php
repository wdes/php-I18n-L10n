<?php
declare(strict_types = 1);
/* The contents of this file is free and unencumbered software released into the
 * public domain.
 * For more information, please refer to <http://unlicense.org/>
 */
namespace Wdes\PIL\Twig\Extension;

use \PHPUnit\Framework\TestCase;
use \Wdes\PIL\Twig\Extension\I18n as ExtensionI18n;
use \Twig\Environment as TwigEnv;
use \Wdes\PIL\plugins\MoReader;
use \Wdes\PIL\Launcher;

/**
 * Test class for Utils
 * @author William Desportes <williamdes@wdes.fr>
 * @license Unlicense
 */
class I18nTest extends TestCase
{
    /**
     * The memory cache
     *
     * @var \Wdes\PIL\Twig\MemoryCache
     */
    private $memoryCache = null;

    /**
     * The TwigEnv object
     *
     * @var TwigEnv
     */
    private $twig = null;

    /**
     * Set up the instance
     *
     * @return void
     */
    public function setUp(): void
    {
        $S       = DIRECTORY_SEPARATOR;
        $dataDir = __DIR__.$S."..".$S."..".$S."data".$S;

        $templatesDir = $dataDir."twig-templates".$S;
        //$tmpDir = $dataDir."twig-templates".$S."tmp".$S;

        $moReader         = new MoReader(
            ["localeDir" => $dataDir]
        );
        $data             = $moReader->readFile($dataDir."abc.mo");
        Launcher::$plugin = $moReader;

        $loader            = new \Twig_Loader_Filesystem();
        $this->memoryCache = new \Wdes\PIL\Twig\MemoryCache();
        $this->twig        = new TwigEnv(
            $loader, [
                'cache' => $this->memoryCache,//$tmpDir | false
                'debug' => true
            ]
        );

        $this->twig->addExtension(new ExtensionI18n());
    }

    /**
     * Test simple translation
     * @return void
     */
    public function testSimpleTranslation(): void
    {
        $template      = $this->twig->createTemplate(
            '{% trans "Translate this" %}'
        );
        $generatedCode = $this->memoryCache->extractDoDisplayFromCache($template);
        $this->assertContains(
            'echo \Wdes\PIL\Launcher::getPlugin()->gettext("Translate this");',
            $generatedCode
        );
        $html = $template->render([]);
        $this->assertEquals("Traduis ça", $html);
        $this->assertNotEmpty($html);
    }

    /**
     * Test simple translation with a comment
     * @return void
     */
    public function testSimpleTranslationWithComment(): void
    {
        $template      = $this->twig->createTemplate(
            '{% trans %}Translate this{% notes %}And note{% endtrans %}'
        );
        $generatedCode = $this->memoryCache->extractDoDisplayFromCache($template);
        $this->assertContains(
            'echo \Wdes\PIL\Launcher::getPlugin()->gettext("Translate this");',
            $generatedCode
        );
        $this->assertContains(
            '// l10n: And note',
            $generatedCode
        );
        $html = $template->render([]);
        $this->assertEquals("Traduis ça", $html);
        $this->assertNotEmpty($html);
    }

    /**
     * Test simple translation with context
     * @return void
     */
    public function testSimpleTranslationWithContext(): void
    {
        $template      = $this->twig->createTemplate(
            '{% trans %}Translate this{% context %}NayanCat{% endtrans %}'
        );
        $generatedCode = $this->memoryCache->extractDoDisplayFromCache($template);

        $this->assertContains(
            'echo \Wdes\PIL\Launcher::getPlugin()->pgettext("NayanCat", "Translate this");',
            $generatedCode
        );
        $html = $template->render([]);
        $this->assertEquals("Traduis ça", $html);
        $this->assertNotEmpty($html);
    }

    /**
     * Test plural translation
     * @return void
     */
    public function testPluralTranslation(): void
    {
        $template      = $this->twig->createTemplate(
            '{% trans %}One person{% plural nbr_persons %}{{ nbr }} persons{% endtrans %}'
        );
        $generatedCode = $this->memoryCache->extractDoDisplayFromCache($template);
        $this->assertContains(
            'echo strtr(\Wdes\PIL\Launcher::getPlugin()->ngettext("One person"'.
            ', "%nbr% persons", abs(($context["nbr_persons"] ?? null))),'.
            ' ["%nbr%" => ($context["nbr"] ?? null)]);',
            $generatedCode
        );
        $html = $template->render(["nbr" => 5]);
        $this->assertEquals("One person", $html);
        $this->assertNotEmpty($html);
    }

    /**
     * Test plural translation with comment
     * @return void
     */
    public function testPluralTranslationWithComment(): void
    {
        $template      = $this->twig->createTemplate(
            '{% trans %}one user likes this.{% plural nbr_persons %}{{ nbr }} users likes this.{% notes %}Number of users{% endtrans %}'
        );
        $generatedCode = $this->memoryCache->extractDoDisplayFromCache($template);
        $this->assertContains(
            'echo strtr(\Wdes\PIL\Launcher::getPlugin()->ngettext('.
            '"one user likes this.", "%nbr% users likes this.",'.
            ' abs(($context["nbr_persons"] ?? null))),'.
            ' ["%nbr%" => ($context["nbr"] ?? null)]);',
            $generatedCode
        );
        $this->assertContains(
            '// l10n: Number of users',
            $generatedCode
        );
        $html = $template->render(["nbr" => 5]);
        $this->assertEquals("one user likes this.", $html);
        $this->assertNotEmpty($html);
    }

    /**
     * Test simple plural translation
     * @return void
     */
    public function testSimplePluralTranslation(): void
    {
        $template      = $this->twig->createTemplate(
            '{% trans %}One person{% plural a %}persons{% endtrans %}'
        );
        $generatedCode = $this->memoryCache->extractDoDisplayFromCache($template);
        $this->assertContains(
            'echo \Wdes\PIL\Launcher::getPlugin()->ngettext("One person", "persons", abs(($context["a"] ?? null)));',
            $generatedCode
        );
        $html = $template->render(["nbr" => 5]);
        $this->assertEquals("One person", $html);
        $this->assertNotEmpty($html);
    }

    /**
      * Test simple plural translation using count


      * @return void
      */
    public function testSimplePluralTranslationCount(): void
    {
        $template      = $this->twig->createTemplate(
            '{% trans %}One person{% plural a.count %}persons{% endtrans %}'
        );
        $generatedCode = $this->memoryCache->extractDoDisplayFromCache($template);
        $this->assertContains(
            'echo \Wdes\PIL\Launcher::getPlugin()->ngettext("One person",'.
            ' "persons", abs(twig_get_attribute($this->env, $this->source,'.
            ' ($context["a"] ?? null), "count", [], "any", false, false, false, 1)));',
            $generatedCode
        );
        $html = $template->render(["a" => ["1", "2"]]);
        $this->assertEquals("One person", $html);
        $this->assertNotEmpty($html);
    }

    /**
     * Test simple plural translation using count and vars
     * @return void
     */
    public function testSimplePluralTranslationCountAndVars(): void
    {
        $template      = $this->twig->createTemplate(
            '{% trans %}One person{% plural a.count %}persons and {{ count }} dogs{% endtrans %}'
        );
        $generatedCode = $this->memoryCache->extractDoDisplayFromCache($template);
        $this->assertContains(
            'echo strtr(\Wdes\PIL\Launcher::getPlugin()->ngettext("One person",'.
            ' "persons and %count% dogs", abs(twig_get_attribute($this->env,'.
            ' $this->source, ($context["a"] ?? null), "count", [], "any", false, false, false, 1))),'.
            ' ["%count%" => abs(twig_get_attribute($this->env,'.
            ' $this->source, ($context["a"] ?? null), "count", [], "any", false, false, false, 1))]);',
            $generatedCode
        );
        $html = $template->render(["a" => ["1", "2"], "nbrdogs" => 3]);
        $this->assertEquals("One person", $html);
        $this->assertNotEmpty($html);
    }

}
