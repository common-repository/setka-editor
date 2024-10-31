<?php
namespace Setka\Editor\Service\SystemReport;

class GlobalVariablesSection extends AbstractVariablesSection implements ArraySectionInterface
{
    /**
     * @var string
     */
    protected $title = 'WordPress Global Variables';

    /**
     * @var array
     */
    private static $variables = array(
        'table_prefix',
        'wp_version',
        'tinymce_version',
        'required_php_version',
        'required_mysql_version',
        'blog_id',
        'locale',
        'is_lynx',
        'is_gecko',
        'is_winIE',
        'is_macIE',
        'is_opera',
        'is_NS4',
        'is_safari',
        'is_chrome',
        'is_iphone',
        'is_IE',
        'is_edge',
        'is_apache',
        'is_IIS',
        'is_iis7',
        'is_nginx',
        'concatenate_scripts',
        'compress_scripts',
        'compress_css',
    );

    public function build(): array
    {
        if (!isset($GLOBALS) || !is_array($GLOBALS)) {
            throw new \Exception('Global variable $GLOBALS does not exists or it is not array');
        }
        $values = array();
        foreach (self::$variables as &$variable) {
            if (!isset($GLOBALS[$variable])) {
                $values[$variable] = self::VARIABLE_DOESNT_EXISTS;
                continue;
            }

            if (is_scalar($GLOBALS[$variable])) {
                $values[$variable] =& $GLOBALS[$variable];
            } else {
                $values[$variable] = self::VARIABLE_DOESNT_SCALAR;
            }
        }
        return $values;
    }
}
