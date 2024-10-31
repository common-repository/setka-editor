<?php
namespace Setka\Editor\Service\AMP;

use Setka\Editor\Service\AMP\AnimationSanitizer\Exceptions\DomainException;
use Setka\Editor\Service\AMP\Traits\XPathFactoryTrait;

class FootnoteSanitizer extends \AMP_Base_Sanitizer
{
    use XPathFactoryTrait;

    const FOOTNOTE_HIDE_CLASS = 'stk-footnote--hide';

    /**
     * @var \DOMElement
     */
    private $post;

    /**
     * @var \DOMElement
     */
    private $footnote;

    /**
     * @var \DOMElement
     */
    private $link;

    /**
     * @var \DOMElement
     */
    private $close;

    /**
     * @var \DOMElement
     */
    private $body;

    /**
     * @inheritdoc
     */
    public function sanitize()
    {
        $posts = $this->setupXPath()->createSetkaPosts();

        foreach ($posts as $this->post) {
            $footNotes = $this->createFootnoteList();

            foreach ($footNotes as $this->footnote) {
                $this->handleFootnote();
            }
        }
    }

    private function handleFootnote()
    {
        try {
            $this->link  = $this->findLink();
            $this->close = $this->findClose();
            $this->body  = $this->findBody();

            $this->footnote->setAttribute('id', $this->buildFootnoteId());
            $this->footnote->removeAttribute('style');
            $this->setupOnAttribute();
        } catch (\Exception $exception) {
        }
    }

    /**
     * @return \DOMNodeList|false
     */
    private function createFootnoteList()
    {
        return $this->xpath->query('.//div[@data-ce-tag="footnote"]', $this->post);
    }

    /**
     * @return \DOMElement
     * @throws \Exception
     */
    private function findLink()
    {
        return $this->find($this->xpath->query(
            './/*[@data-stk-footnote-link="' . $this->getFootnoteHash().'"]',
            $this->post
        ));
    }

    /**
     * @return \DOMElement
     * @throws \Exception
     */
    private function findClose()
    {
        return $this->find($this->xpath->query(
            './/div[@class="stk-footnote__close"]',
            $this->footnote
        ));
    }

    /**
     * @return \DOMElement
     * @throws \Exception
     */
    private function findBody()
    {
        return $this->find($this->xpath->query(
            './/div[@class="stk-footnote__body"]',
            $this->footnote
        ));
    }

    /**
     * @param mixed $queryResult
     *
     * @return \DOMElement
     * @throws \Exception
     */
    private function find($queryResult)
    {
        if ($queryResult) {
            return $this->getFirstElement($queryResult);
        }
        throw new \Exception();
    }

    /**
     * @throws \Exception
     */
    private function setupOnAttribute()
    {
        $value = 'tap:' . $this->buildFootnoteId() . '.toggleClass(class="' . self::FOOTNOTE_HIDE_CLASS. '")';
        foreach (array($this->footnote, $this->link, $this->close) as $element) {
            $element->setAttribute('on', $value);
            $this->setupTabindexAndRoleAttribute($element);
        }
        $this->body->setAttribute(
            'on',
            'tap:' . $this->buildFootnoteId() . '.toggleClass(class="stk-footnote--silent")'
        );
        $this->setupTabindexAndRoleAttribute($this->body);
    }

    /**
     * @param \DOMElement $element
     */
    private function setupTabindexAndRoleAttribute(\DOMElement $element)
    {
        $element->setAttribute('tabindex', 0);
        $element->setAttribute('role', 'button');
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function getFootnoteHash()
    {
        $id = $this->footnote->getAttribute('data-stk-footnote-body');

        if (is_string($id) && !empty($id)) {
            return $id;
        }
        throw new \Exception();
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function buildFootnoteId()
    {
        return 'stk-footnote-' . $this->getFootnoteHash();
    }

    /**
     * @param \DOMNodeList $list
     *
     * @return \DOMElement
     *
     * @throws DomainException
     */
    private function getFirstElement(\DOMNodeList $list)
    {
        if (1 !== count($list)) {
            throw new \LengthException('List contains invalid amount of elements. Expected: 1. Actual: ' . count($list));
        }

        $element = $list->item(0);

        if (is_a($element, \DOMElement::class)) {
            /**
             * @var $element \DOMElement
             */
            return $element;
        }
        throw new DomainException($element);
    }
}
