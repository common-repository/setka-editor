<?php
namespace Setka\Editor\Admin\Pages;

class BaseEntity
{
    /**
     * @var string
     */
    protected $nonce;

    /**
     * @return string
     */
    public function getNonce()
    {
        return $this->nonce;
    }

    /**
     * @param string $nonce
     * @return $this
     */
    public function setNonce($nonce)
    {
        $this->nonce = $nonce;
        return $this;
    }
}
