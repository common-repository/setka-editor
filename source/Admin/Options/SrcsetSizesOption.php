<?php
namespace Setka\Editor\Admin\Options;

use Korobochkin\WPKit\Options\AbstractOption;
use Setka\Editor\Plugin;
use Setka\Editor\Service\Config\ImageSizesConfig;
use Symfony\Component\Validator\Constraints;

class SrcsetSizesOption extends AbstractOption
{
    public function __construct()
    {
        $this
            ->setName(Plugin::_NAME_ . '_srcset_sizes')
            ->setDefaultValue(array(
                'medium',
                'large',
                ImageSizesConfig::SIZE_FULL,
            ));
    }

    /**
     * @inheritdoc
     */
    public function buildConstraint()
    {
        return array(
            new Constraints\NotNull(),
            new Constraints\Choice(array(
                'choices' => array_keys(ImageSizesConfig::getAllImageSizes()),
                'multiple' => true,
                'strict' => true,
            )),
        );
    }

    /**
     * @return array
     */
    public function get()
    {
        $value = parent::get();
        $full  = ImageSizesConfig::SIZE_FULL;
        $key   = array_search($full, $value, true);

        if (false === $key) {
            $value[] = $full;
        }

        return $value;
    }
}
