<?php

namespace SPHERE\Application\Education\ClassRegister\Digital\Frontend;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Api\Education\ClassRegister\ApiDigital;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer as ConsumerGatekeeper;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Comment;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\ResizeVertical;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullLeft;
use SPHERE\Common\Frontend\Layout\Repository\Thumbnail;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Table\Structure\TableData;

class FrontendDownload extends FrontendCourseContent
{
    /**
     * @param null $DivisionCourseId
     * @param null $BackDivisionCourseId
     * @param string $BasicRoute
     *
     * @return string
     *
     * @noinspection PhpUnused
     */
    public function frontendDownload(
        $DivisionCourseId = null,
        $BackDivisionCourseId = null,
        string $BasicRoute = '/Education/ClassRegister/Digital/Teacher'
    ): string {
        $icon = new Download();
        $name = 'Download';
        $Route = '/Education/ClassRegister/Digital/Download';
        $content = ApiDigital::receiverBlock($this->loadDownloadContent($DivisionCourseId), 'DownloadContent');

        return Digital::useFrontend()->getStage($DivisionCourseId, $BasicRoute, $Route, $icon, $name, $content, $BackDivisionCourseId);
    }

    /**
     * @param $DivisionCourseId
     *
     * @return string
     */
    public function loadDownloadContent($DivisionCourseId): string
    {
        $content = '';
        $icon = new Download();
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            if ($tblDivisionCourse->getType()->getIsCourseSystem()) {
                $text = 'Kursliste';
                $printLink = (new Link((new Thumbnail(
                    FileSystem::getFileLoader('/Common/Style/Resource/SSWPrint.png'), 'Kursheft'))->setPictureHeight(),
                    '/Api/Document/Standard/CourseContent/Create', null, array(
                        'DivisionCourseId' => $DivisionCourseId
                    )))->setExternal();
            } else {
                if (($isCoreGroup = $tblDivisionCourse->getTypeIdentifier() == TblDivisionCourseType::TYPE_CORE_GROUP)) {
                    $text = 'Stammgruppenliste';
                } else {
                    $text = 'Klassenliste';
                }

                $isCourseSystem = DivisionCourse::useService()->getIsCourseSystemByStudentsInDivisionCourse($tblDivisionCourse);

                if ($isCourseSystem) {
                    $printLink = null;
                } else {
                    $printLink = (new Link((new Thumbnail(
                        FileSystem::getFileLoader('/Common/Style/Resource/SSWPrint.png'),
                        $isCoreGroup ?  'Stammgruppen&shy;tagebuch' : ' Klassen&shy;tagebuch'))->setPictureHeight(),
                        '/Api/Document/Standard/ClassRegister/Create', null, array(
                            'DivisionCourseId' => $DivisionCourseId
                        )))->setExternal();
                }
            }

            $content = new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Danger('Die dauerhafte Speicherung des Excel-Exports ist datenschutzrechtlich nicht zulässig!',
                                new Exclamation())
                        ),
                        new LayoutColumn(
                            new Link((new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/SSWAgreement.png'), $text . ' Einverständnis&shy;erklärung'))->setPictureHeight(),
                                '/Api/Reporting/Standard/Person/AgreementClassList/Download', null, array(
                                    'DivisionCourseId' => $DivisionCourseId
                                ))
                            , 2),
                        new LayoutColumn(
                            new Link((new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/SSWMedical.png'), $text . ' Krankenakte'))->setPictureHeight(),
                                '/Api/Reporting/Standard/Person/MedicalRecordClassList/Download', null, array(
                                    'DivisionCourseId' => $DivisionCourseId
                                ))
                            , 2),
                        new LayoutColumn(
                            (new Link((new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/SSWUser.png'), $text . ' individuelle Schülerliste'))->setPictureHeight(),
                                ApiDigital::getEndpoint()
                            ))->ajaxPipelineOnClick(ApiDigital::pipelineLoadIndividualDownloadContent($DivisionCourseId))
                            , 2),
                        new LayoutColumn(
                            new Link((new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/SSWUser.png'), $text . ' Standard Schülerliste'))->setPictureHeight(),
                                '/Api/Reporting/Standard/Person/ClassList/Download', null, array(
                                    'DivisionCourseId' => $DivisionCourseId
                                ))
                            , 2),
                        new LayoutColumn(
                            new Link((new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/SSWAbsence.png'), $text . ' zeugnis&shy;relevante Fehlzeiten'))->setPictureHeight(),
                                '/Api/Reporting/Standard/Person/ClassRegister/Absence/Download', null, array(
                                    'DivisionCourseId' => $DivisionCourseId
                                ))
                            , 2),
                        new LayoutColumn(
                            new Link((new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/SSWAbsence.png'), $text . ' Monatliche Fehlzeiten'))->setPictureHeight(),
                                '/Api/Reporting/Standard/Person/ClassRegister/AbsenceMonthly/Download', null, array(
                                    'DivisionCourseId' => $DivisionCourseId
                                ))
                            , 2),

                    )),
                    new LayoutRow(array(
                        new LayoutColumn(
                            $printLink
                            , 2),
                    ))
                )),
                ConsumerGatekeeper::useService()->getConsumerBySessionIsConsumer(TblConsumer::TYPE_SACHSEN, 'EVOSG')
                    ? new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(
                        new Link((new Thumbnail(
                            FileSystem::getFileLoader('/Common/Style/Resource/SSWUser.png'), 'Individuelle Klassenliste'))->setPictureHeight(),
                            '/Api/Reporting/Custom/IndividualClassRegisterDownload', null, array(
                                'DivisionCourseId' => $DivisionCourseId,
                                'Type'    => 'downloadClassList'
                            ))
                        , 2),
                    new LayoutColumn(
                        new Link((new Thumbnail(
                            FileSystem::getFileLoader('/Common/Style/Resource/SSWAgreement.png'), 'Individuelle Unterschriftenliste'))->setPictureHeight(),
                            '/Api/Reporting/Custom/IndividualClassRegisterDownload', null, array(
                                'DivisionCourseId' => $DivisionCourseId,
                                'Type'    => 'downloadSignList'
                            ))
                        , 2),
                    new LayoutColumn(
                        new Link((new Thumbnail(
                            FileSystem::getFileLoader('/Common/Style/Resource/SSWUser.png'), 'Individuelle Klassenliste Fremdsprachen'))->setPictureHeight(),
                            '/Api/Reporting/Custom/IndividualClassRegisterDownload', null, array(
                                'DivisionCourseId' => $DivisionCourseId,
                                'Type'    => 'downloadElectiveClassList'
                            ))
                        , 2),
                    new LayoutColumn(
                        new Link((new Thumbnail(
                            FileSystem::getFileLoader('/Common/Style/Resource/SSWUser.png'), 'Individuelle Telefonliste'))->setPictureHeight(),
                            '/Api/Reporting/Custom/IndividualClassRegisterDownload', null, array(
                                'DivisionCourseId' => $DivisionCourseId,
                                'Type'    => 'downloadClassPhoneList'
                            ))
                        , 2),
                )), new Title($icon . ' Individual Download'))
                    : null,
                ConsumerGatekeeper::useService()->getConsumerBySessionIsConsumer(TblConsumer::TYPE_SACHSEN, 'KG')
                    ? new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(
                        new Link((new Thumbnail(
                            FileSystem::getFileLoader('/Common/Style/Resource/SSWAgreement.png'), 'Individuelle Unterschriftenliste'))->setPictureHeight(),
                            '/Api/Reporting/Custom/Kreuzgymnasium/Common/SignList/Download', null, array(
                                'DivisionCourseId' => $DivisionCourseId
                            ))
                        , 2),
                    new LayoutColumn(
                        new Link((new Thumbnail(
                            FileSystem::getFileLoader('/Common/Style/Resource/SSWAgreement.png'), 'Individuelle Unterschriftenliste Querformat'))->setPictureHeight(),
                            '/Api/Reporting/Custom/Kreuzgymnasium/Common/SignList/Download', null, array(
                                'DivisionCourseId' => $DivisionCourseId,
                                'isLandscape' => 1
                            ))
                        , 2),
                )), new Title($icon . ' Individual Download'))
                    : null,
            ));
        }

        return $content;
    }

    /**
     * @param $DivisionCourseId
     *
     * @return string
     */
    public function loadDownloadFilter($DivisionCourseId): string
    {
        $columns = Digital::useService()->getStudentListColumnAll();

        $count = 1;
        $dataList = [];
        $global = $this->getGlobal();
        if (($tblPerson = Account::useService()->getPersonByLogin())
            && ($tblStudentListColumn = Digital::useService()->getStudentListColumn($tblPerson))
        ) {
            foreach ($tblStudentListColumn->getColumns() as $identifier => $value) {
                $global->POST['Data']['Columns'][$identifier] = $value;

                $dataList[] = $this->addField($identifier, $columns[$identifier], $count++, str_contains($identifier, 'FreeText'));
                unset($columns[$identifier]);
            }
            foreach ($tblStudentListColumn->getFreeTexts() as $identifier => $value) {
                $global->POST['Data']['FreeTexts'][$identifier] = $value;
            }
        } else {
            $global->POST['Data']['Columns']['Number'] = 1;
            $global->POST['Data']['Columns']['LastName'] = 1;
            $global->POST['Data']['Columns']['FirstName'] = 1;
        }
        $global->savePost();

        foreach ($columns as $identifier => $name) {
            $dataList[] = $this->addField($identifier, $name, $count++, str_contains($identifier, 'FreeText'));
        }

        $headerList = [];
        $headerList['number'] = '#';
        $headerList['check'] = 'Auswahl';
        $headerList['column'] = 'Spalte';
        $headerList['name'] = 'Name';

        $table = new TableData($dataList, null, $headerList,
            array(
                'rowReorderColumn' => 2,
                'ExtensionRowReorder' => array(
                    'Enabled' => true,
                    'Url'     => '/Api/Education/ClassRegister/StudentListFilter/Reorder',
                ),
                'columnDefs' => array(
                    array('orderable' => false, 'targets' => array(1, 2)),
                    array('width' => '30px', 'targets' => array(0, 1)),
                ),
                'pageLength' => -1,
                'paging' => false,
                'info' => false,
                'searching' => false,
                'responsive' => false,
            )
        );

        $form = new Form(new FormGroup(new FormRow(array(
            new FormColumn((new Primary('Anzeigen', ApiDigital::getEndpoint()))->ajaxPipelineOnClick(ApiDigital::pipelineSaveDownloadFilter($DivisionCourseId))),
            new FormColumn($table),
        ))));

        return new Title('Individueller Download der Schülerliste', 'Spalten-Auswahl und Spalten-Sortierung')
            . $form->disableSubmitAction();
    }

    /**
     * @param string $identifier
     * @param string $name
     * @param int $count
     * @param bool $isFreeText
     *
     * @return array
     */
    private function addField(string $identifier, string $name, int $count, bool $isFreeText = false): array
    {
        return [
            'number' => $count,
//            'check' => new CheckBox(@"Data[Check][$identifier]", ' ', 1) . new HiddenField(@"Data[All][$identifier]"),
            'check' => new CheckBox(@"Data[Columns][$identifier]", ' ', 1),
            'column' => new PullClear(new PullLeft(new ResizeVertical() . ' ' . $name)),
            'name' => $isFreeText ? new PullLeft(new TextField(@"Data[FreeTexts][$identifier]", null, null, new Comment())) : ''
        ];
    }

    /**
     * @param $DivisionCourseId
     *
     * @return string
     */
    public function loadIndividualDownloadContent($DivisionCourseId): string
    {
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            list($headerList, $dataList) = Digital::useService()->getStudentListDownloadContent($tblDivisionCourse);

            $backButton = (new Standard('Zurück', ApiDigital::getEndpoint(), new ChevronLeft(), [], 'Zurück zur Spalten-Auswahl und Spalten-Sortierung'))
                ->ajaxPipelineOnClick(ApiDigital::pipelineLoadIndividualDownloadContent($DivisionCourseId));

            // Excel - Download
            $excel = new Primary('Als Excel herunterladen', '/Api/Reporting/Standard/Person/ClassList/DownloadIndividual',
                new Download(), ['DivisionCourseId' => $DivisionCourseId]);

            // PDF - Download DocumentBuilder
            $pdf = (new Primary('Als PDF herunterladen', '/Api/Document/Standard/ClassRegister/StudentList/Individual/Create',
                new Download(), ['DivisionCourseId' => $DivisionCourseId]))->setExternal();

            return new Danger('Die dauerhafte Speicherung des Exports ist datenschutzrechtlich nicht zulässig!', new Exclamation())
                . $backButton . $excel . $pdf
                . new TableData($dataList, null, $headerList, array(
                    'pageLength' => -1,
                    'paging' => false,
                    'info' => false,
                    'searching' => false,
                    'responsive' => false,
                    'ordering' => false
                ));
        }

        return '';
    }
}