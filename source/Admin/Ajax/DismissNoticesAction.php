<?php
namespace Setka\Editor\Admin\Ajax;

use Korobochkin\WPKit\AlmostControllers\AbstractAction;
use Korobochkin\WPKit\AlmostControllers\ActionInterface;
use Korobochkin\WPKit\Notices\NoticeInterface;
use Korobochkin\WPKit\Options\OptionInterface;
use Korobochkin\WPKit\PostMeta\PostMetaInterface;
use Korobochkin\WPKit\Transients\TransientInterface;
use Symfony\Component\HttpFoundation\Response;

class DismissNoticesAction extends AbstractAction implements ActionInterface
{
    public function __construct()
    {
        $this
            ->setEnabledForNotLoggedIn(false)
            ->setEnabledForLoggedIn(true);
    }

    /**
     * @inheritdoc
     */
    public function handleRequest()
    {
        if (!current_user_can('manage_options')) {
            $this->getResponse()->setStatusCode(Response::HTTP_UNAUTHORIZED);
            return $this;
        }

        $noticeClass = $this->getRequest()->request->get('noticeClass');

        if (!$noticeClass) {
            $this->getResponse()->setStatusCode(Response::HTTP_BAD_REQUEST);
            return $this;
        }

        try {
            /**
             * @var $notice NoticeInterface
             */
            $notice = $this->findNotice(
                $this->container->getParameter('wp.plugins.setka_editor.all_notices'),
                $noticeClass
            );

            if (!$notice) {
                $this->getResponse()->setStatusCode(Response::HTTP_NOT_FOUND);
                return $this;
            }
        } catch (\Exception $exception) {
            $this->getResponse()->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
            return $this;
        }

        try {
            $notice->disable();
        } catch (\Exception $exception) {
            $this->getResponse()->setStatusCode(Response::HTTP_BAD_REQUEST);
            return $this;
        }

        $this->getResponse()->setStatusCode(Response::HTTP_OK);
        return $this;
    }

    /**
     * @param array $notices
     * @param string $noticeClass
     */
    private function findNotice(array $notices, $noticeClass)
    {
        foreach ($notices as $reference) {
            if ($noticeClass === (string) $reference) {
                return $this->get($reference);
            }
        }
        return false;
    }
}
