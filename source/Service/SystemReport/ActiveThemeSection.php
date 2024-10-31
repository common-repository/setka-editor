<?php
namespace Setka\Editor\Service\SystemReport;

class ActiveThemeSection extends AbstractSection implements ArraySectionInterface
{
    /**
     * @var string
     */
    protected $title = 'Active theme';

    /**
     * @var string[]
     */
    protected $buildMethods = array('wp_get_theme', 'get_stylesheet');

    /**
     * @var string[]
     */
    private static $keys = array(
        'update',
        'theme_root',
        'errors',
        'stylesheet',
        'template',
        'parent',
        'theme_root_uri',
        'textdomain_loaded',
    );

    /**
     * @var string[]
     */
    private static $headers = array(
        'Name',
        'ThemeURI',
        'Description',
        'Author',
        'AuthorURI',
        'Version',
        'Template',
        'Status',
        'Tags',
        'TextDomain',
        'DomainPath',
        'RequiresWP',
        'RequiresPHP',
    );

    public function build(): array
    {
        $theme = wp_get_theme(get_stylesheet());

        $data = array(
            'headers' => array(),
        );

        foreach (self::$keys as $key) {
            $data[$key] = $theme->{$key};
        }

        foreach (self::$headers as $header) {
            $data['headers'][$header] = $theme->get($header);
        }

        return $data;
    }
}
