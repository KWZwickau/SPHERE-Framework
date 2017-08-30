<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 26.07.2016
 * Time: 13:33
 */

namespace SPHERE\Application\Education\Certificate\PrintCertificate;

use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Certificate\Generate\Generate;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendPrintCertificate()
    {

        $Stage = new Stage('Zeugnis', 'Übersicht (nicht gedruckte Zeugnisse)');
        $Stage->addButton(new Standard('Historie Personen', '/Education/Certificate/PrintCertificate/History', null, array(),
            'Bereits gedruckte Zeugnisse pro Schüler ansehen und drucken'));
        $Stage->addButton(new Standard('Historie Klassen', '/Education/Certificate/PrintCertificate/History/Division', null, array(),
            'Bereits gedruckte Zeugnisse pro Klasse ansehen und drucken'));

        // freigebene und nicht gedruckte Zeugnisse
        $tableContent = array();
        $tblPrepareStudentList = Prepare::useService()->getPrepareStudentAllWhere(true, false);
        $prepareList = array();
        if ($tblPrepareStudentList) {
            foreach ($tblPrepareStudentList as $tblPrepareStudent) {
                if (($tblPerson = $tblPrepareStudent->getServiceTblPerson())
                    && ($tblPrepare = $tblPrepareStudent->getTblPrepareCertificate())
                    && $tblPrepareStudent->getServiceTblCertificate()
                ) {
                    if (!isset($prepareList[$tblPrepare->getId()])) {
                        $prepareList[$tblPrepare->getId()] = $tblPrepare;
                        break;
                    }
                }
            }
        }
        // alle automatisch freigebenen Zeugnisse
        if ($tblGenerateCertificateList = Generate::useService()->getGenerateCertificateAll()) {
            foreach ($tblGenerateCertificateList as $tblGenerateCertificate) {
                if (($tblCertificateType = $tblGenerateCertificate->getServiceTblCertificateType())
                    && $tblCertificateType->isAutomaticallyApproved()
                    && ($tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate))
                ) {
                    foreach ($tblPrepareList as $tblPrepareCertificate) {
                        if (($tblPrepareStudentList = Prepare::useService()->getPrepareStudentAllByPrepare($tblPrepareCertificate))) {
                            foreach ($tblPrepareStudentList as $tblPrepareStudent) {
                                if (($tblPerson = $tblPrepareStudent->getServiceTblPerson())
                                    && ($tblPrepare = $tblPrepareStudent->getTblPrepareCertificate())
                                    && $tblPrepareStudent->getServiceTblCertificate()
                                    && !$tblPrepareStudent->isPrinted()
                                ) {
                                    if (!isset($prepareList[$tblPrepare->getId()])) {
                                        $prepareList[$tblPrepare->getId()] = $tblPrepare;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        /** @var TblPrepareCertificate $tblPrepare */
        foreach ($prepareList as $tblPrepare) {
            if (($tblDivision = $tblPrepare->getServiceTblDivision())) {
                $tableContent[] = array(
                    'Year' => $tblDivision->getServiceTblYear()
                        ? $tblDivision->getServiceTblYear()->getDisplayName() : '',
                    'Date' => $tblPrepare->getDate(),
                    'Division' => $tblDivision->getDisplayName(),
                    'CertificateType' =>
                        ($tblCertificateType = $tblPrepare->getCertificateType()) ? $tblCertificateType->getName() : '',
                    'Name' => $tblPrepare->getName(),
                    'Option' => new Standard(
                        'Zeugnisse herunterladen und revisionssicher speichern',
                        '/Education/Certificate/PrintCertificate/Confirm',
                        new Download(),
                        array(
                            'PrepareId' => $tblPrepare->getId(),
                        ))
                );
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
                                    'Date' => 'Zeugnisdatum',
                                    'Division' => 'Klasse',
                                    'Name' => 'Name',
                                    'CertificateType' => 'Zeugnistyp',
                                    'Option' => ''
                                ),
                                array(
                                    'order' => array(
                                        array(0, 'desc'),
                                        array(1, 'desc'),
                                        array(2, 'asc'),
                                        array(3, 'asc'),
                                    ),
                                    'columnDefs' => array(
                                        array('type' => 'natural', 'targets' => 2),
                                    )
                                )
                            )
                        ))
                    ))
                ))
            ))
        );

        return $Stage;
    }

    /**
     * @param null $PrepareId
     *
     * @return Stage
     */
    public function frontendConfirmPrintCertificate($PrepareId = null)
    {

        $Stage = new Stage('Zeugnis', 'Herunterladen und revisionssicher abspeichern');

        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblDivision = $tblPrepare->getServiceTblDivision())
        ) {

            $Stage->addButton(new Standard(
                'Zurück', '/Education/Certificate/PrintCertificate', new ChevronLeft()
            ));

            if (($tblCertificateType = $tblPrepare->getCertificateType())
                && $tblCertificateType->isAutomaticallyApproved()
            ) {
                $isAutomaticallyApproved = true;
            } else {
                $isAutomaticallyApproved = false;
            }

            $data = array();
            if (($tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision))) {
                foreach ($tblPersonList as $tblPerson) {
                    if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))
                        && $tblPrepareStudent->getServiceTblCertificate()
                        && !$tblPrepareStudent->isPrinted()
                    ) {
                        if ($tblPrepareStudent->isApproved()
                            || $isAutomaticallyApproved
                        ) {
                            $data[] = $tblPerson->getLastFirstName();
                        }
                    }
                }
            }

            $Stage->setContent(
                new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                    new Panel(
                        'Klasse',
                        $tblDivision->getDisplayName(),
                        Panel::PANEL_TYPE_INFO
                    ),
                    new Panel(
                        new Question() . ' Dieses Zeugnis wirklich drucken und revisionssicher abspeichern?',
                        $data,
                        Panel::PANEL_TYPE_DANGER,
                        (new External(
                            'Ja',
                            '/Api/Education/Certificate/Generator/DownLoadMultiPdf',
                            new Ok(),
                            array(
                                'PrepareId' => $tblPrepare->getId(),
                            ),
                            'Zeugnisse herunterladen und revisionssicher abspeichern'))
                            ->setRedirect('/Education/Certificate/PrintCertificate', 60)
                        . new Standard(
                            'Nein', '/Education/Certificate/PrintCertificate', new Disable()
                        )
                    ),
                )))))
            );
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger(new Ban() . ' Das Zeugnis konnte nicht gefunden werden'),
                        new Redirect('/Education/Certificate/PrintCertificate', Redirect::TIMEOUT_ERROR)
                    )))
                )))
            );
        }

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendPrintCertificateHistory()
    {

        $Stage = new Stage('Zeugnis', 'Person auswählen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/PrintCertificate', new ChevronLeft()
        ));

        $tblFileList = Storage::useService()->getCertificateRevisionFileAll();

        $personList = array();
        if ($tblFileList) {
            foreach ($tblFileList as $tblFile) {
                if (strpos($tblFile->getTblDirectory()->getIdentifier(), 'TBL-PERSON-ID:') !== false) {
                    $personId = substr($tblFile->getTblDirectory()->getIdentifier(), strlen('TBL-PERSON-ID:'));
                    if (Person::useService()->getPersonById($personId)) {
                        if (isset($personList[$personId])) {
                            $personList[$personId] = $personList[$personId] + 1;
                        } else {
                            $personList[$personId] = 1;
                        }
                    }
                }
            }
        }

        $dataList = array();
        foreach ($personList as $key => $value) {
            if (($tblPerson = Person::useService()->getPersonById($key))) {
                $dataList[] = array(
                    'Name' => $tblPerson->getLastFirstName(),
                    'Address' => $tblPerson->fetchMainAddress() ? $tblPerson->fetchMainAddress()->getGuiString() : '',
                    'Count' => $value,
                    'Option' => new Standard(
                        '',
                        '/Education/Certificate/PrintCertificate/History/Person',
                        new Select(),
                        array(
                            'PersonId' => $tblPerson->getId()
                        ),
                        'Person auswählen'
                    )
                );
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            empty($dataList)
                                ? new Warning('Keine Zeugnisse vorhanden', new Exclamation())
                                : new TableData(
                                $dataList, null, array(
                                    'Name' => 'Name',
                                    'Address' => 'Adresse',
                                    'Count' => 'Zeugnisse',
                                    'Option' => ''
                                )
                            )
                        ))
                    ))
                ))
            ))
        );

        return $Stage;
    }

    /**
     * @param null $PersonId
     *
     * @return Stage|string
     */
    public function frontendPrintCertificateHistoryPerson($PersonId = null)
    {

        $Stage = new Stage('Zeugnis', 'Auswahl');
        $Stage->addButton(new Standard('Zurück', '/Education/Certificate/PrintCertificate/History', new ChevronLeft()));

        if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $tblFileList = Storage::useService()->getCertificateRevisionFileAllByPerson($tblPerson);
            $dataList = array();
            if ($tblFileList) {
                foreach ($tblFileList as $tblFile) {
                    $name = explode(' - ', $tblFile->getName());

                    if ($tblFile->getEntityUpdate()){
                        $date = $tblFile->getEntityUpdate()->format('d.m.Y');
                        $isChanged = true;
                    } else {
                        $date = $tblFile->getEntityCreate()->format('d.m.Y');
                        $isChanged = false;
                    }
                    if (count($name) >= 3) {
                        $dataList[] = array(
                            'Year' => $name[0],
                            'Date' => $date,
                            'Certificate' => $name[2],
                            'Changed' => $isChanged ? 'geändert': '',
                            'Option' => new External(
                                'Herunterladen',
                                '/Api/Education/Certificate/Generator/Download',
                                new Download(),
                                array(
                                    'FileId' => $tblFile->getId(),
                                ),
                                'Zeugnis herunterladen')
                        );
                    }
                }
            }

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Person',
                                    $tblPerson->getLastFirstName(),
                                    Panel::PANEL_TYPE_INFO
                                ),
                                empty($dataList)
                                    ? new Warning('Keine Zeugnisse vorhanden', new Exclamation())
                                    : new TableData(
                                    $dataList, null, array(
                                    'Year' => 'Jahr',
                                    'Date' => 'Gedruckt am',
                                    'Certificate' => 'Zeugnis',
                                    'Changed' => 'geändert',
                                    'Option' => ''
                                ),
                                    array(
                                        'order' => array(
                                            array(0, 'desc'),
                                            array(1, 'asc')
                                        ),
                                        'columnDefs' => array(
                                            array('type' => 'de_date', 'targets' => 1)
                                        )
                                    )
                                )
                            ))
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {

            return $Stage
            . new Danger('Person nicht gefunden', new Ban())
            . new Redirect('/Education/Certificate/PrintCertificate/History', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @return Stage
     */
    public function frontendPrintCertificateHistoryDivision()
    {

        $Stage = new Stage('Zeugnis', 'Klasse auswählen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/PrintCertificate', new ChevronLeft()
        ));

        $tblDivisionList = Division::useService()->getDivisionAll();

        $divisionTable = array();
        if ($tblDivisionList) {
            /** @var TblDivision $tblDivision */
            foreach ($tblDivisionList as $tblDivision) {
                if (Prepare::useService()->getPrepareAllByDivision($tblDivision)) {
                    $divisionTable[] = array(
                        'Year' => $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getDisplayName() : '',
                        'Type' => $tblDivision->getTypeName(),
                        'Division' => $tblDivision->getDisplayName(),
                        'Option' => new Standard(
                            '', '/Education/Certificate/PrintCertificate/History/Division/Selected', new Select(),
                            array(
                                'DivisionId' => $tblDivision->getId()
                            ),
                            'Auswählen'
                        )
                    );
                }
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($divisionTable, null, array(
                                'Year' => 'Schuljahr',
                                'Type' => 'Schulart',
                                'Division' => 'Klasse',
                                'Option' => ''
                            ), array(
                                'order' => array(
                                    array('0', 'desc'),
                                    array('2', 'asc'),
                                ),
                                'columnDefs' => array(
                                    array('type' => 'natural', 'targets' => 2),
                                )
                            ))
                        ))
                    ))
                ), new Title(new Select() . ' Auswahl'))
            ))
        );

        return $Stage;
    }

    /**
     * @param null $DivisionId
     *
     * @return Stage|string
     */
    public function frontendPrintCertificateHistorySelectedDivision($DivisionId = null)
    {

        $Stage = new Stage('Zeugnis', 'Auswahl');
        $Stage->addButton(new Standard('Zurück', '/Education/Certificate/PrintCertificate/History/Division', new ChevronLeft()));

        if (($tblDivision = Division::useService()->getDivisionById($DivisionId))) {
            $dataList = array();
            if ($tblPrepareList = Prepare::useService()->getPrepareAllByDivision($tblDivision)){
                foreach ($tblPrepareList as $tblPrepare){
                    if (Prepare::useService()->isPreparePrinted($tblPrepare)) {
                        $dataList[] = array(
                            'Year' => $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getDisplayName() : '',
                            'Date' => $tblPrepare->getDate(),
                            'Name' => $tblPrepare->getName(),
                            'Option' => new External(
                                'Herunterladen',
                                '/Api/Education/Certificate/Generator/History/DownloadZip',
                                new Download(),
                                array(
                                    'PrepareId' => $tblPrepare->getId(),
                                ),
                                'Zeugnis herunterladen')
                        );
                    }
                }
            }

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Klasse',
                                    $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_INFO
                                ),
                                empty($dataList)
                                    ? new Warning('Keine Zeugnisse vorhanden', new Exclamation())
                                    : new TableData(
                                    $dataList, null, array(
                                    'Year' => 'Jahr',
                                    'Date' => 'Zeugnisdatum',
                                    'Name' => 'Name',
                                    'Option' => ''
                                ),
                                    array(
                                        'order' => array(
                                            array(0, 'desc'),
                                            array(1, 'desc')
                                        ),
                                        'columnDefs' => array(
                                            array('type' => 'de_date', 'targets' => 1),
                                            array('type' => 'natural', 'targets' => 1),
                                        )
                                    )
                                )
                            ))
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {

            return $Stage
                . new Danger('Klasse nicht gefunden', new Ban())
                . new Redirect('/Education/Certificate/PrintCertificate/History/Division', Redirect::TIMEOUT_ERROR);
        }
    }
}