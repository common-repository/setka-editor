<?php
namespace Setka\Editor\Admin\Options;

use Korobochkin\WPKit\Options\AbstractOption;
use Setka\Editor\Plugin;
use Symfony\Component\Validator\Constraints;

/**
 * Class TokenOption
 *
 * Also known as License key (but by historical reasons it named as token in DB and code).
 *
 * @package Setka\Editor\Admin\Options\Token
 */
class TokenOption extends AbstractOption
{
    public function __construct()
    {
        $this
            ->setName(Plugin::_NAME_ . '_token')
            ->setDefaultValue('');
    }

    /**
     * @inheritdoc
     */
    public function buildConstraint()
    {
        return array(
            new Constraints\NotBlank(array(
                'message' => __('Please fill in your license key.', Plugin::NAME)
            )),
            new Constraints\Length(array(
                'min' => 32,
                'max' => 32,
                'exactMessage' => __('License key should have exactly {{ limit }} characters.', Plugin::NAME),
                'allowEmptyString' => false,
            )),
        );
    }
}
