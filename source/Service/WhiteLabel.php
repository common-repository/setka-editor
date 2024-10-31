<?php
namespace Setka\Editor\Service;

use Setka\Editor\Admin\Options\PlanFeatures\PlanFeaturesOption;
use Setka\Editor\Admin\Options\WhiteLabelOption;
use Setka\Editor\PostMetas\UseEditorPostMeta;

class WhiteLabel
{
    /**
     * @var WhiteLabelOption
     */
    private $whiteLabelOption;

    /**
     * @var UseEditorPostMeta
     */
    private $useEditorPostMeta;

    /**
     * @var PlanFeaturesOption
     */
    private $planFeaturesOption;

    /**
     * WhiteLabel constructor.
     *
     * @param WhiteLabelOption $whiteLabelOption
     * @param UseEditorPostMeta $useEditorPostMeta
     * @param PlanFeaturesOption $planFeaturesOption
     */
    public function __construct(
        WhiteLabelOption $whiteLabelOption,
        UseEditorPostMeta $useEditorPostMeta,
        PlanFeaturesOption $planFeaturesOption
    ) {
        $this->whiteLabelOption   = $whiteLabelOption;
        $this->useEditorPostMeta  = $useEditorPostMeta;
        $this->planFeaturesOption = $planFeaturesOption;
    }

    /**
     * Add white label.
     *
     * @param $content string Post content.
     *
     * @throws \Exception
     *
     * @return string Post content with white label.
     */
    public function addLabel($content)
    {
        try {
            $post = $this->getPost();
            if ($this->whiteLabelOption->get() && $this->useEditorPostMeta->setPostId($post->ID)->get()) {
                $whiteLabelValue = $this->planFeaturesOption->get();

                $content .= $whiteLabelValue['white_label_html'];
            }
        } catch (\Exception $exception) {
        }
        return $content;
    }

    /**
     * @return \WP_Post
     */
    private function getPost()
    {
        return WPPostFactory::createFromGlobals();
    }
}
