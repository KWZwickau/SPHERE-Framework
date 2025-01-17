<?php

namespace SPHERE\Application\Transfer\Untis\Export\Meta;

use SPHERE\Application\Transfer\Indiware\Export\Export;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{
    /**
     * @param bool $IsAllYears
     * @param string|null $YearId
     *
     * @return Stage
     */
    public function frontendPrepare(bool $IsAllYears = false, ?string $YearId = null): Stage
    {
        $Stage = new Stage('Untis', 'Datentransfer');
        $Stage->addButton(new Standard('Zurück', '/Transfer/Untis/Export', new ChevronLeft()));
        $Stage->setMessage('Exportvorbereitung / Kursauswahl');

        $Stage->setContent(Export::useFrontend()->getCourseSelectStageContent(
            '/Transfer/Untis/Export/Meta',
            '/Api/Transfer/Untis/Meta/Download',
            $IsAllYears,
            $YearId
        ));

        return $Stage;
    }
}