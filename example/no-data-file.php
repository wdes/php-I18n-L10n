<?php

declare(strict_types = 1);

// Can be removed :)

require_once __DIR__ . '/../vendor/autoload.php';

use Wdes\phpI18nL10n\plugins\MoReader;
use Wdes\phpI18nL10n\Launcher;
use Wdes\phpI18nL10n\Twig\Extension\I18n as ExtensionI18n;
use Twig\Environment as TwigEnvironment;
use Twig\Loader\ArrayLoader as TwigLoader;

$moReader = new MoReader();
$moReader->setTranslations(
    [
        'Homepage' => 'Page d\'accueil',
    ]
);
// Load the translation plugin
Launcher::setPlugin($moReader);

$twig = new TwigEnvironment(new TwigLoader());
$twig->addExtension(new ExtensionI18n());
// You can use a file instead, see the example using a mo file
$templateContents = <<<HTML
<html>
    <title>{% trans %}Homepage{% endtrans %}</title>
    <body>
        {% trans %}Homepage{% endtrans %}
    </body>
</html>
HTML;
echo $twig->createTemplate($templateContents)->render([]);
