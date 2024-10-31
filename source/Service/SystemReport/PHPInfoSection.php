<?php
namespace Setka\Editor\Service\SystemReport;

class PHPInfoSection extends AbstractSection implements StringSectionInterface
{
    /**
     * @var string
     */
    protected $title = 'PHP Info';

    /**
     * @var array
     */
    protected $buildMethods = array('ob_start', 'phpinfo', 'ob_get_contents', 'ob_get_clean');

    public function build(): string
    {
        ob_start();
        // phpcs:disable WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_phpinfo
        phpinfo();
        // phpcs:enable
        $value = ob_get_contents();
        ob_end_clean();
        return $value;
    }
}
