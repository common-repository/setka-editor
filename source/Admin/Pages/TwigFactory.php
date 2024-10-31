<?php
namespace Setka\Editor\Admin\Pages;

use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Extension\HttpFoundationExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\Form\FormRenderer;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\RuntimeLoader\FactoryRuntimeLoader;

class TwigFactory
{
    /**
     * Creates \Twig\Environment instance.
     *
     * @param string|false $cache Path to folder with cache files or false if cache disabled.
     * @param string $templatesPath Path to folder with Twig templates.
     *
     * @return \Twig\Environment
     */
    public static function create($cache, $templatesPath)
    {
        $cacheTwig = ($cache) ? $cache . 'twig/' : false;

        $reflection = new \ReflectionClass(HttpFoundationExtension::class);

        $twig = new Environment(
            new FilesystemLoader(
                array(
                    $templatesPath,
                    dirname(dirname($reflection->getFileName())) . '/Resources/views/Form',
                )
            ),
            array(
                'cache' => $cacheTwig,
            )
        );

        $formEngine = new TwigRendererEngine(array('form_div_layout.html.twig'), $twig);
        $twig->addRuntimeLoader(
            new FactoryRuntimeLoader(
                array(
                    FormRenderer::class => function () use ($formEngine) {
                        return new FormRenderer($formEngine);
                    },
                )
            )
        );

        $twig->addExtension(new FormExtension());
        $twig->addExtension(new TranslationExtension());

        return $twig;
    }
}
