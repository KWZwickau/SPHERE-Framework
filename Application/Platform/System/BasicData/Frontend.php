<?php

namespace SPHERE\Application\Platform\System\BasicData;

use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\FileUpload;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Holiday;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Platform\System\BasicData
 */
class Frontend extends Extension implements IFrontendInterface
{
    /**
     * @param Stage $Stage
     */
    private function menuButton(Stage $Stage)
    {
        $Stage->addButton(new Standard('Unterrichtsfreie Tage', new Link\Route(__NAMESPACE__ . '\Holiday'), new Holiday()));
    }

    /**
     * @return Stage
     */
    public function frontendHoliday()
    {
        $Stage = new Stage('Grunddaten', 'Unterrichtsfreie Tage');
        $this->menuButton($Stage);

        $data = array();
        if (($tblHolidayList = BasicData::useService()->getHolidayAll())) {
            foreach ($tblHolidayList as $tblHoliday) {
                $data[] = array(
                    'FromDate' => $tblHoliday->getFromDate(),
                    'ToDate' => $tblHoliday->getToDate(),
                    'Name' => $tblHoliday->getName(),
                    'Type' => ($tblHolidayType = $tblHoliday->getTblHolidayType()) ? $tblHolidayType->getName() : '',
                    'State' => ($tblState = $tblHoliday->getTblState()) ? $tblState->getName() : '',
                );
            }
        }

        $Stage->setContent(
            (new Standard('Import', new Link\Route(__NAMESPACE__ . '\Holiday\Import'), new Upload()))
            . (new TableData(
                $data,
                null,
                array(
                    'FromDate' => 'Datum von',
                    'ToDate' => 'Datum bis',
                    'Name' => 'Name',
                    'Type' => 'Typ',
                    'State' => 'Bundesland',
                ),
                array(
                    'order' => array(
                        array(0, 'desc')
                    ),
                    'columnDefs' => array(
                        array('type' => 'de_date', 'targets' => array(0,1))
                    ),
                )
            ))
        );

        return  $Stage;
    }

    /**
     * @param null $File
     *
     * @return Stage
     */
    public function frontendImportHoliday($File = null)
    {
        $Stage = new Stage('Import', 'Unterrichtsfreie Tage');
        $Stage->addButton(new Standard('Zurück', new Link\Route(__NAMESPACE__ . '\Holiday'), new ChevronLeft()));

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Well(
                                BasicData::useService()->createHolidaysFromFile(new Form(
                                    new FormGroup(
                                        new FormRow(
                                            new FormColumn(
                                                new FileUpload('File', 'Datei auswählen', 'Datei auswählen', null,
                                                    array('showPreview' => false))
                                            )
                                        )
                                    )
                                    , new Primary('Hochladen')
                                ), $File)
                                . new Warning(new Exclamation() . ' Erlaubte Dateitypen: Excel (XLS,XLSX)')
                            )
                        ))
                    )
                )
            )
        );

        return  $Stage;
    }
}