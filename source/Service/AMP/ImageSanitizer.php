<?php
namespace Setka\Editor\Service\AMP;

use Setka\Editor\Admin\Options\SrcsetSizesOption;
use Setka\Editor\Exceptions\LogicException;
use Setka\Editor\PostMetas\ImageAttachmentMetadataPostMeta;
use Setka\Editor\Service\AMP\Traits\XPathFactoryTrait;
use Setka\Editor\Service\Config\ImageSizesConfig;

class ImageSanitizer extends \AMP_Base_Sanitizer
{
    use XPathFactoryTrait;

    /**
     * @var \DOMElement Current image.
     */
    private $image;

    /**
     * @var integer Id of current image (attachment).
     */
    private $id;

    /**
     * @var array
     */
    private $srcsetSizes;

    /**
     * @var ImageAttachmentMetadataPostMeta
     */
    private $imageMetadataPostMeta;

    private function initialize()
    {
        $map = array(
            SrcsetSizesOption::class,
            ImageAttachmentMetadataPostMeta::class,
        );

        foreach ($map as $service) {
            if (!isset($this->args[$service])) {
                throw new LogicException('Not all required services passed to sanitizer.');
            }
        }

        $this->srcsetSizes           = $this->args[SrcsetSizesOption::class]->get();
        $this->imageMetadataPostMeta = $this->args[ImageAttachmentMetadataPostMeta::class];
    }

    /**
     * @inheritdoc
     */
    public function sanitize()
    {
        try {
            $this->initialize();
        } catch (\Exception $exception) {
            return;
        }

        /**
         * @var $posts \DOMNodeList
         * @var $post \DOMElement
         * @var $images \DOMNodeList
         */
        $posts = $this->setupXPath()->createSetkaPosts();

        foreach ($posts as $post) {
            $images = $this->createImages($post);

            foreach ($images as $this->image) {
                try {
                    $this->validateImage()
                         ->removeStkReset()
                         ->removeSizes()
                         ->detectCurrentImageId()
                         ->widthAndHeightAttributes()
                         ->srcSet();
                } catch (\Exception $exception) {
                    continue;
                }
            }
        }
    }

    /**
     * Detect current img id.
     *
     * ID stored in data-image-id attribute if post created in Setka Editor 2 version, otherwise in id.
     *
     * @throws \RuntimeException If id not found.
     *
     * @return $this
     */
    public function detectCurrentImageId()
    {
        if ($this->image->hasAttribute('data-image-id')) {
            $id = $this->image->getAttribute('data-image-id');
        } else {
            $idAttr = $this->image->getAttribute('id');
            $id     = filter_var($idAttr, FILTER_SANITIZE_NUMBER_INT); // attribute format id='image-1325'
        }

        if (!is_string($id) || empty($id)) {
            throw new \RuntimeException();
        }

        $id = absint($id);

        if ($id <= 0) {
            throw new \RuntimeException();
        }

        $this->id = $id;

        return $this;
    }

    /**
     * Remove stk-reset CSS class.
     *
     * @return $this
     */
    public function removeStkReset()
    {
        $classes = trim($this->image->getAttribute('class'));

        if (empty($classes)) {
            return $this;
        }

        $classes = explode(' ', $classes);

        if (empty($classes)) {
            return $this;
        }

        $index = array_search('stk-reset', $classes, true);

        if (false !== $index) {
            unset($classes[$index]);

            if (empty($classes)) {
                $this->image->removeAttribute('class');
            } else {
                $this->image->setAttribute('class', implode(' ', $classes));
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function removeSizes()
    {
        $this->image->removeAttribute('sizes');
        return $this;
    }

    /**
     * Add width and height attributes for img.
     *
     * @throws \RuntimeException
     *
     * @return $this
     */
    public function widthAndHeightAttributes()
    {
        if (!isset($this->id)) {
            throw new \RuntimeException();
        }

        if ($this->image->hasAttribute('width') || $this->image->hasAttribute('height')) {
            return $this;
        }

        $meta = wp_get_attachment_metadata($this->id);

        if (isset($meta['width']) && isset($meta['height'])) {
            $this->image->setAttribute('width', $meta['width']);
            $this->image->setAttribute('height', $meta['height']);
        }

        return $this;
    }

    /**
     * Add srcset attribute for img.
     *
     * @throws \RuntimeException
     *
     * @return $this
     */
    public function srcSet()
    {
        if (!isset($this->id)) {
            throw new \RuntimeException();
        }

        if (empty(trim($this->image->getAttribute('srcset')))) {
            $this->image->removeAttribute('srcset');
        }

        $meta  = $this->imageMetadataPostMeta->setPostId($this->id)->get();
        $sizes =& $meta[ImageAttachmentMetadataPostMeta::SIZES];

        // Skip .gif files
        if ('gif' === pathinfo($this->imageMetadataPostMeta->getFile(), PATHINFO_EXTENSION)) {
            return $this;
        }

        if ($this->imageMetadataPostMeta->isValid() && count($sizes) >= 1) {
            $srcSetValues = array();
            reset($this->srcsetSizes);

            foreach ($this->srcsetSizes as $size) {
                if (isset($sizes[$size])) {
                    // Skip size if it has the same width as 'full'
                    if ($sizes[$size][ImageAttachmentMetadataPostMeta::WIDTH] === $meta[ImageAttachmentMetadataPostMeta::WIDTH]) {
                        continue;
                    }

                    $data = wp_get_attachment_image_src($this->id, $size);

                    $srcSetValues[] = self::generateSrcSetPartValue(
                        $data[0],
                        $sizes[$size][ImageAttachmentMetadataPostMeta::WIDTH]
                    );
                } elseif (ImageSizesConfig::SIZE_FULL === $size) {
                    $data = wp_get_attachment_url($this->id);

                    $srcSetValues[] = self::generateSrcSetPartValue(
                        $data,
                        $meta[ImageAttachmentMetadataPostMeta::WIDTH]
                    );
                }
            }

            if (!empty($srcSetValues)) {
                $this->image->setAttribute('srcset', implode(', ', $srcSetValues));
            }
        }

        return $this;
    }

    /**
     * @param string $url
     * @param integer $width
     *
     * @return string
     */
    private static function generateSrcSetPartValue($url, $width)
    {
        return $url . ' ' . $width . 'w';
    }

    /**
     * @param \DOMElement $post
     *
     * @return \DOMNodeList
     */
    private function createImages(\DOMElement $post)
    {
        return $this->xpath->query('.//img', $post);
    }

    /**
     * @throws \DomainException
     * @return $this
     */
    private function validateImage()
    {
        if (!is_a($this->image, \DOMElement::class)) {
            throw new \DomainException();
        }

        $this->id = null;

        return $this;
    }
}
