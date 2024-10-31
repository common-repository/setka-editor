<?php
namespace Setka\Editor\Admin\Pages\Files;

use Korobochkin\WPKit\Pages\SubMenuPage;
use Korobochkin\WPKit\Pages\Views\TwigPageView;
use Setka\Editor\Admin\Options\Files\FilesOption;
use Setka\Editor\Admin\Options\Files\FileSyncFailureOption;
use Setka\Editor\Admin\Options\Files\ServiceSwitchOption;
use Setka\Editor\Admin\Options\Files\FileSyncStageOption;
use Setka\Editor\Admin\Options\Files\UseLocalFilesOption;
use Setka\Editor\Admin\Service\FilesManager\AssetsStatus;
use Setka\Editor\Plugin;

class FilesPage extends SubMenuPage
{
    /**
     * @var AssetsStatus
     */
    protected $assetsStatus;

    public function __construct()
    {
        $this->setParentSlug(Plugin::NAME);
        $this->setPageTitle(__('Files', Plugin::NAME));
        $this->setMenuTitle($this->getPageTitle());
        $this->setCapability('manage_options');
        $this->setMenuSlug(Plugin::NAME . '-files');

        $this->setName('files');

        $view = new TwigPageView();
        $view->setTemplate('admin/settings/setka-editor/files/page.html.twig');
        $this->setView($view);
    }

    public function lateConstruct()
    {
        try {
            $context = array(
                'options' => $this->lateConstructOptions(),
                'posts' => $this->assetsStatus->getCountersByStatus(),
            );
        } catch (\Exception $exception) {
            $context['posts'] = array();
        }

        $this->getView()->setContext($context);

        return $this;
    }

    protected function lateConstructOptions()
    {
        $options = array();

        $option = new FilesOption();
        $value  = $option->get();
        if (is_array($value) && !empty($value)) {
            $value = '[contains list of files]';
        } else {
            $value = 'UNKNOWN VALUE';
        }
        $key           = new \ReflectionClass($option);
        $key           = $key->getShortName();
        $options[$key] = $value;

        // phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_var_export
        $option        = new FileSyncFailureOption();
        $key           = new \ReflectionClass($option);
        $key           = $key->getShortName();
        $options[$key] = var_export($option->get(), true);
        // phpcs:enable

        // phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_var_export
        $option        = new ServiceSwitchOption();
        $key           = new \ReflectionClass($option);
        $key           = $key->getShortName();
        $options[$key] = var_export($option->get(), true);
        // phpcs:enable

        // phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_var_export
        $option        = new FileSyncStageOption();
        $key           = new \ReflectionClass($option);
        $key           = $key->getShortName();
        $options[$key] = var_export($option->get(), true);
        // phpcs:enable

        // phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_var_export
        $option        = new UseLocalFilesOption();
        $key           = new \ReflectionClass($option);
        $key           = $key->getShortName();
        $options[$key] = var_export($option->get(), true);
        // phpcs:enable

        return $options;
    }

    /**
     * @param AssetsStatus $assetsStatus
     */
    public function setAssetsStatus(AssetsStatus $assetsStatus)
    {
        $this->assetsStatus = $assetsStatus;
    }
}
