<?php
namespace SPHERE\Application\Document\Storage;

use SPHERE\Application\Document\Storage\Service\Entity\TblDirectory;
use SPHERE\Application\Document\Storage\Service\Entity\TblFile;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Table\Repository\Title;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Document\Explorer
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendManager()
    {

        $Stage = new Stage('Manager');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn($this->guiPanel('Links'), 6),
                        new LayoutColumn($this->guiPanel('Rechts'), 6),
                    ))
                ))
            )

        );

        return $Stage;
    }

    /**
     * @param $Name
     *
     * @return array
     */
    private function guiPanel($Name)
    {

        return array(
            (new TableData(array(new TblDirectory()), new Title('Verzeichnisse', $Name), array(),
                array('pageLength' => -1)))->__toString(),
            (new TableData(array(new TblFile()), new Title('Dateien', $Name), array(),
                array('pageLength' => -1)))->__toString(),
        );
    }
}
