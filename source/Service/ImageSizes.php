<?php
namespace Setka\Editor\Service;

use Setka\Editor\Admin\Options\SrcsetSizesOption;
use Setka\Editor\Plugin;
use Symfony\Component\HttpFoundation\Request;

class ImageSizes
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var SrcsetSizesOption
     */
    private $srcsetSizesOption;

    /**
     * @var array
     */
    private $callbacks = array('seekInRequest', 'seekInQuery');

    /**
     * @var string
     */
    private $needleKey = Plugin::_NAME_;

    /**
     * @var string
     */
    private $needle = 'on';

    /**
     * ImageSizes constructor.
     *
     * @param Request $request
     * @param SrcsetSizesOption $srcsetSizesOption
     */
    public function __construct(Request $request, SrcsetSizesOption $srcsetSizesOption)
    {
        $this->request           = $request;
        $this->srcsetSizesOption = $srcsetSizesOption;
    }

    /**
     * @param array $sizes
     *
     * @return array
     */
    public function sizes(array $sizes)
    {
        if ($this->isEditorRequest()) {
            return $this->prepareSizes($sizes);
        }
        return $sizes;
    }

    /**
     * @param array $old
     * @return array
     */
    private function prepareSizes(array &$old)
    {
        $required = $this->srcsetSizesOption->get();

        foreach ($required as $size) {
            if (isset($old[$size])) {
                continue;
            } else {
                $old[$size] = ucfirst($size);
            }
        }

        return $old;
    }

    /**
     * @return bool
     */
    private function isEditorRequest()
    {
        $amount = count($this->callbacks);

        for ($i = 0; $i < $amount; $i++) {
            try {
                return call_user_func(array($this, $this->callbacks[$i]));
            } catch (\Exception $exception) {
                if ($i + 1 < $amount) { // There is more callbacks in stack.
                    continue;
                } else {
                    return false;
                }
            }
        }
    }

    /**
     * @throws \RuntimeException
     * @return bool
     */
    private function seekInRequest()
    {
        return $this->needle === $this->getFromRequest($this->needleKey);
    }

    /**
     * @throws \RuntimeException
     * @return bool
     */
    private function seekInQuery()
    {
        $query = $this->getFromRequest('query');
        return is_array($query) && isset($query[$this->needleKey]) && $this->needle === $query[$this->needleKey];
    }

    /**
     * @param $name
     * @throws \RuntimeException
     * @return mixed
     */
    private function getFromRequest($name)
    {
        $value = $this->request->request->get($name);
        if (is_null($value)) {
            throw new \RuntimeException();
        }
        return $value;
    }
}
