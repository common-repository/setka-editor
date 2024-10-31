<?php
namespace Setka\Editor\Service;

use Setka\Editor\Admin\Options\PlanFeatures\PlanFeaturesOption;
use Setka\Editor\Admin\Options\WhiteLabelOption;
use Setka\Editor\PostMetas\UseEditorPostMeta;

class WhiteLabelFactory
{
    /**
     * @param WhiteLabelOption $whiteLabelOption
     * @param DataFactory $dataFactory
     * @param PlanFeaturesOption $planFeaturesOption
     *
     * @return WhiteLabel
     */
    public static function create(
        WhiteLabelOption $whiteLabelOption,
        DataFactory $dataFactory,
        PlanFeaturesOption $planFeaturesOption
    ) {
        return new WhiteLabel(
            $whiteLabelOption,
            $dataFactory->create(UseEditorPostMeta::class),
            $planFeaturesOption
        );
    }
}
