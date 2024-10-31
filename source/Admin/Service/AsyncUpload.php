<?php
namespace Setka\Editor\Admin\Service;

use Setka\Editor\Plugin;
use Symfony\Component\HttpFoundation\Request;

class AsyncUpload
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var array
     */
    private $file;

    /**
     * AsyncUpload constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param array $file
     * @return array
     */
    public function preFilter(array $file)
    {
        $this->file =& $file;
        if ($this->isSetka()) {
            $this->updateName();
        }
        return $this->file;
    }

    private function updateName()
    {
        if ($this->shouldUpdateName()) {
            $this->file['name'] = $this->getNameFromRequest();
        }
    }

    /**
     * @return bool
     */
    private function isSetka()
    {
        return $this->request->request->get(Plugin::_NAME_) === 'on';
    }

    /**
     * @return bool
     */
    private function shouldUpdateName()
    {
        return isset($this->file['name']) &&
               'blob' === $this->file['name'] &&
               $this->getNameFromRequest();
    }

    /**
     * @return string|null
     */
    private function getNameFromRequest()
    {
        return $this->request->request->get('name');
    }
}
