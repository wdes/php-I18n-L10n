<?php

declare(strict_types = 1);

// Can be removed :)

require_once __DIR__ . '/../vendor/autoload.php';

use Wdes\phpI18nL10n\plugins\MoReader;
use Wdes\phpI18nL10n\Launcher;
use Wdes\phpI18nL10n\Twig\Extension\I18n as ExtensionI18n;
use Twig\Environment as TwigEnvironment;
use Twig\Loader\FilesystemLoader as TwigLoaderFilesystem;

$dataDir  = __DIR__ . '/locale/';
$moReader = new MoReader();
$moReader->readFile($dataDir . 'fr.mo'); // Load the file you want (a specific language for example)
// Load the translation plugin
Launcher::setPlugin($moReader);

$loader = new TwigLoaderFilesystem([ __DIR__ . '/templates/' ]); // Load all templates from the dir
$twig   = new TwigEnvironment($loader);

$twig->addExtension(new ExtensionI18n());
echo $twig->render(
    'homepage.twig', // Can be found in the templates directory
    [
        'keyForTwig' => 'theValue', // Just an example line ;)
        'say' => 'Hello world',
    ]
);
