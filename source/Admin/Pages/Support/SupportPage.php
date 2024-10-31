<?php
namespace Setka\Editor\Admin\Pages\Support;

use Korobochkin\WPKit\Pages\SubMenuPage;
use Korobochkin\WPKit\Pages\Views\TwigPageView;
use Setka\Editor\Admin\Prototypes\Pages\PrepareTabsTrait;
use Setka\Editor\Plugin;
use Setka\Editor\Service\SystemReport\SystemReport;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class SupportPage extends SubMenuPage
{
    use PrepareTabsTrait;

    /**
     * @var SystemReport
     */
    private $systemReport;

    public function __construct()
    {
        $this->setParentSlug(Plugin::NAME);
        $this->setPageTitle(__('Support', Plugin::NAME));
        $this->setMenuTitle($this->getPageTitle());
        $this->setCapability('manage_options');
        $this->setMenuSlug(Plugin::NAME . '-support');

        $this->setName('support');

        $view = new TwigPageView();
        $view->setTemplate('admin/settings/support/page.html.twig');
        $this->setView($view);
    }

    /**
     * @inheritDoc
     */
    public function lateConstruct()
    {
        $this->prepareTabs();

        $this->setForm($this->getFormFactory()->create(SupportType::class, new Support()));

        $response = $this->handleRequest();

        if ($response) {
            $response->send();
            exit();
        }

        $this->getView()->setContext(array(
            'page' => $this,
            'translations' => $this->buildTranslations(),
            'form' => $this->getForm()->createView(),
            'report' => $this->systemReport,
        ));

        return $this;
    }

    /**
     * @inheritDoc
     * @return Response|void
     */
    public function handleRequest()
    {
        $this->form->handleRequest($this->request);

        if (!$this->form->isSubmitted() || !$this->form->isValid()) {
            return;
        }

        $content = '';

        foreach ($this->systemReport->walk() as $name => $value) {
            $content .= $name . PHP_EOL . PHP_EOL . $value . PHP_EOL . PHP_EOL . PHP_EOL;
        }

        $response = new Response($content);

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $this->systemReport->buildFilename() . '.txt'
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    /**
     * @return array
     */
    private function buildTranslations()
    {
        return array(
            'description' => __('If you encounter any issues with Setka Editor, please click on the “Generate Diagnostic Report” button and contact <a href="mailto:support@tiny.cloud">support@tiny.cloud</a> with the file attached so we can investigate. The report contains information about your Setka Editor plugin parameters, Setka Editor posts, and AMP styles, filesystem, options, PHP, etc.', Plugin::NAME),
        );
    }

    /**
     * @param SystemReport $systemReport
     */
    public function setSystemReport(SystemReport $systemReport)
    {
        $this->systemReport = $systemReport;
    }
}
