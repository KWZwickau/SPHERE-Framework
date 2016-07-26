<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 26.07.2016
 * Time: 13:33
 */

namespace SPHERE\Application\Education\Certificate\PrintCertificate;

use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{

    public function frontendPrintCertificate()
    {

        $Stage = new Stage('Zeugnis', 'Übersicht (nicht gedruckte Zeugnisse)');

        // freigeben und nicht gedruckte Zeugnisse
        $tableContent = array();
        $tblPrepareStudentList = Prepare::useService()->getPrepareStudentAllWhere(true, false);
        if ($tblPrepareStudentList) {
            foreach ($tblPrepareStudentList as $tblPrepareStudent) {
                if (($tblPerson = $tblPrepareStudent->getServiceTblPerson())) {
                    $tblPrepare = $tblPrepareStudent->getTblPrepareCertificate();
                    $tblDivision = $tblPrepare->getServiceTblDivision();
                    $tblYear = $tblDivision ? $tblDivision->getServiceTblYear() : false;


                    $tableContent[] = array(
                        'Year' => $tblYear ? $tblYear->getDisplayName() : '',
                        'Division' => $tblDivision ? $tblDivision->getDisplayName() : '',
                        'Student' => $tblPerson->getLastFirstName(),
                        'Option' => new External(
                            'Zeugnis drucken (herunterladen)',
                            '/Api/Education/Certificate/Generator/Create',
                            new Download(),
                            array(
                                'PrepareId' => $tblPrepare->getId(),
                                'PersonId' => $tblPerson->getId(),
                            ), false)
                    );
                }
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            empty($tableContent)
                                ? new Warning('Keine Zeugnisse zum Druck verfügbar', new Exclamation())
                                : new TableData(
                                $tableContent,
                                null,
                                array(
                                    'Year' => 'Schuljahr',
                                    'Division' => 'Klasse',
                                    'Student' => 'Schüler',
                                    'Option' => ''
                                ),
                                array(
                                    'order' => array(
                                        array(0, 'desc'),
                                        array(1, 'asc'),
                                        array(2, 'asc'),
                                    ),
                                )
                            )
                        ))
                    ))
                ))
            ))
        );

        return $Stage;
    }
}