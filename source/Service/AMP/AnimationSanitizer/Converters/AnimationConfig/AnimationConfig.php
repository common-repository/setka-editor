<?php
namespace Setka\Editor\Service\AMP\AnimationSanitizer\Converters\AnimationConfig;

use Setka\Editor\Exceptions\RuntimeException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnimationConfig implements AnimationConfigInterface
{
    /**
     * @var array
     */
    private $data = array();

    /**
     * AnimationConfig constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(array('id', 'selector', 'switch'));
        $resolver->setAllowedTypes('id', 'string');
        $resolver->setAllowedTypes('selector', 'string');
        $resolver->setAllowedTypes('switch', 'array');
        $data = $resolver->resolve($data);

        $resolver = new OptionsResolver();
        $resolver->setRequired(array('media', 'duration', 'delay', 'keyframes'));
        $resolver->setAllowedTypes('media', 'string');
        $resolver->setAllowedTypes('duration', 'float');
        $resolver->setAllowedTypes('delay', 'float');
        $resolver->setAllowedTypes('keyframes', 'array');
        foreach ($data['switch'] as &$switchCase) {
            $switchCase = $resolver->resolve($switchCase);
        }

        $this->data = $data;
    }

    /**
     * @inheritDoc
     */
    public function getID()
    {
        return $this->data['id'];
    }

    /**
     * @inheritDoc
     */
    public function asArray()
    {
        return $this->data;
    }

    /**
     * @@inheritDoc
     */
    public function get($key)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }
        throw new RuntimeException('Requested key does not exist: "'. $key . '"');
    }
}
