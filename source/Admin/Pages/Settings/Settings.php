<?php
namespace Setka\Editor\Admin\Pages\Settings;

use Setka\Editor\Admin\Pages\BaseEntity;

class Settings extends BaseEntity
{
    const STYLES_MODE_COMBINED = 'combined';

    const STYLES_MODE_STANDALONE = 'standalone';

    const STYLES_MODE_STANDALONE_CRITICAL = 'standalone_critical';

    /**
     * @var array
     */
    protected $postTypes = array();

    /**
     * @var array
     */
    protected $roles = array();

    /**
     * @var ImageSize[]
     */
    protected $srcsetSizes = array();

    /**
     * @var string
     */
    protected $endpoint;

    /**
     * @var string
     */
    private $stylesMode;

    /**
     * @var boolean
     */
    private $forceUseSetkaCDN;

    /**
     * @var boolean
     */
    protected $whiteLabel = false;

    /**
     * @return array
     */
    public function getPostTypes()
    {
        return $this->postTypes;
    }

    /**
     * @return array
     */
    public function getPostTypesAsArray()
    {
        $value = array();

        foreach ($this->postTypes as $postType) {
            $value[] = $postType->getId();
        }

        return $value;
    }

    /**
     * @param array $postTypes
     * @return $this
     */
    public function setPostTypes(array $postTypes)
    {
        $this->postTypes = $postTypes;
        return $this;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     * @return $this
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @return ImageSize[]
     */
    public function getSrcsetSizes()
    {
        return $this->srcsetSizes;
    }

    /**
     * @return array
     */
    public function getSrcsetSizesAsArray()
    {
        $value = array();

        foreach ($this->srcsetSizes as $size) {
            $value[] = $size->getId();
        }

        return $value;
    }

    /**
     * @param ImageSize[] $srcsetSizes
     *
     * @return $this
     */
    public function setSrcsetSizes(array $srcsetSizes)
    {
        $this->srcsetSizes = $srcsetSizes;
        return $this;
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * @param string $endpoint
     * @return $this
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    /**
     * @return string
     */
    public function getStylesMode()
    {
        return $this->stylesMode;
    }

    /**
     * @param string $stylesMode
     *
     * @return $this
     */
    public function setStylesMode($stylesMode)
    {
        $this->stylesMode = $stylesMode;
        return $this;
    }

    /**
     * @return bool
     */
    public function isForceUseSetkaCDN()
    {
        return $this->forceUseSetkaCDN;
    }

    /**
     * @param bool $forceUseSetkaCDN
     *
     * @return $this
     */
    public function setForceUseSetkaCDN($forceUseSetkaCDN)
    {
        $this->forceUseSetkaCDN = $forceUseSetkaCDN;
        return $this;
    }

    /**
     * @return bool
     */
    public function isWhiteLabel()
    {
        return $this->whiteLabel;
    }

    /**
     * @param bool $whiteLabel
     * @return $this
     */
    public function setWhiteLabel($whiteLabel)
    {
        $this->whiteLabel = $whiteLabel;
        return $this;
    }
}
