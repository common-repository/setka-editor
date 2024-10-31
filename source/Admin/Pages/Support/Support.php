<?php
namespace Setka\Editor\Admin\Pages\Support;

use Setka\Editor\Admin\Pages\BaseEntity;

class Support extends BaseEntity
{
    const FORMAT_TEXT = 'text';

    const FORMAT_JSON = 'json';

    /**
     * @var string
     */
    private $format = self::FORMAT_TEXT;

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param string $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
        return $this;
    }
}
