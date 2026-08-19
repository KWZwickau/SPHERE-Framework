<?php

namespace SPHERE\Application\Education\Certificate\Setting;

use DateTime;
use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Api\Education\Certificate\Setting\ApiPreviewCertificate;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\Icon\Repository\Repeat;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class FrontendPreviewCertificate extends Extension implements IFrontendInterface
{
    /**
     * @param $Filter
     *
     * @return Stage
     */
    public function frontend($Filter = null): Stage
    {
        $stage = new Stage('Zeugnisvorlagen Vorschau');
        $stage = Frontend::setSettingMenue($stage, 'Preview');

        $stage->setContent(
            new Panel(new Filter() . ' Filter', $this->form($Filter), Panel::PANEL_TYPE_PRIMARY)
            . ApiPreviewCertificate::receiverContent($this->loadContent($Filter), 'Content')
        );

        return $stage;
    }

    /**
     * @param $Filter
     *
     * @return Form
     */
    public function form($Filter): Form
    {
        $tblConsumerList = [];
        if (($tblConsumer = Consumer::useService()->getConsumerBySession())) {
            if ($tblConsumer->getAcronym() == 'REF') {
                $tblConsumerList = Consumer::useService()->getConsumerAll(true);
            } else {
                $tblConsumerList[] = $tblConsumer;
            }
        }

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    (new SelectBox('Filter[SchoolType]', 'Schulart', array('{{ ShortName }} {{ Name }}' => Type::useService()->getTypeAll())))
                        ->ajaxPipelineOnChange(ApiPreviewCertificate::pipelineLoadContent($Filter))
                    , 6),
                new FormColumn(
                    (new SelectBox('Filter[Consumer]', 'Mandant', array('{{ Acronym }} {{ Name }}' => $tblConsumerList)))
                        ->ajaxPipelineOnChange(ApiPreviewCertificate::pipelineLoadContent($Filter))
                    , 6),
            ))
        )));
    }

    /**
     * @param $Filter
     *
     * @return string
     */
    public function loadContent($Filter = null): string
    {
//        if (!($tblConsumer = Consumer::useService()->getConsumerBySession())) {
//            return '';
//        }

        $tblSchoolTypeFilter = isset($Filter['SchoolType']) ? Type::useService()->getTypeById($Filter['SchoolType']) : null;

        if (isset($Filter['Consumer'])
            && ($tblConsumerFilter = Consumer::useService()->getConsumerById($Filter['Consumer'], true))
        ) {
            $tblCertificateList = Generator::useService()->getTemplateAllByConsumer($tblConsumerFilter);
        } else {
            /** @noinspection PhpRedundantOptionalArgumentInspection */
            $tblCertificateList = Generator::useService()->getTemplateAllByConsumer(null);
//            if ($tblConsumer->getAcronym() == 'REF') {
//                $tblCertificateList = Generator::useService()->getCertificateAll();
//            } else {
//                $tblCertificateList = Generator::useService()->getTemplateAllByConsumer(null);
//            }
//
//            if (!$tblCertificateList) {
//                $tblCertificateList = [];
//            }
//            if (($tblCertificatesConsumer = Generator::useService()->getTemplateAllByConsumer($tblConsumer))) {
//                $tblCertificateList = array_merge($tblCertificatesConsumer, $tblCertificateList);
//            }
        }

        if (!$tblCertificateList) {
            $tblCertificateList = [];
        }
        $selectList = [];
        /** @var TblCertificate $tblCertificate */
        foreach ($tblCertificateList as $tblCertificate) {
            // filtern nach Schulart
            $tblSchoolType = $tblCertificate->getServiceTblSchoolType();
            if (($tblSchoolType
                && $tblSchoolTypeFilter
                && $tblSchoolTypeFilter->getId() != $tblSchoolType->getId())
                    || (!$tblSchoolType && $tblSchoolTypeFilter)
            ) {
               continue;
            }

            $selectList[$tblCertificate->getId()] =
                (($tblConsumerCertificate = $tblCertificate->getServiceTblConsumer(true)) ? $tblConsumerCertificate->getAcronym() . ' - ' : '')
                . $tblCertificate->getName()
                . (($description = $tblCertificate->getDescription()) ? ' - ' . $description : '')
                . (($certificateNumber = $tblCertificate->getCertificateNumber()) ? ' ' . $certificateNumber : '');
        }

        $global = $this->getGlobal();
        $global->POST['Data']['FirstName'] = 'Maximilian';
        $global->POST['Data']['LastName'] = 'Mustermann';
        $global->POST['Data']['Division'] = '8a';
        $global->POST['Data']['Year'] = '2025/26';
        $global->POST['Data']['Birthday'] = '01.01.2010';
        $global->POST['Data']['Birthplace'] = 'Chemnitz';
        $global->POST['Data']['Company'] = 'Schulzentrum Niederdorf';

        $global->savePost();

        $form = (new Form(new FormGroup([
            new FormRow([
                new FormColumn(
                    (new SelectBox('Data[tblCertificate]', 'Zeugnisvorlage', $selectList))
                        ->ajaxPipelineOnChange([ApiPreviewCertificate::pipelineLoadCertificatePreview(), ApiPreviewCertificate::pipelineLoadDownloadButton()])
                , 9),
                new FormColumn([
                    (new Container('&nbsp;'))->setStyle(['padding-top: 5px;']),
                    (new Standard('', ApiPreviewCertificate::getEndpoint(), new Repeat(), [], 'Aktualisieren'))
                        ->ajaxPipelineOnClick([ApiPreviewCertificate::pipelineLoadCertificatePreview(), ApiPreviewCertificate::pipelineLoadDownloadButton()])
                ], 1),
                new FormColumn([
                    (new Container('&nbsp;'))->setStyle(['padding-top: 5px;']),
                    ApiPreviewCertificate::receiverContent('', 'DownloadButton')
                ], 1),
            ]),
            new FormRow([
                new FormColumn(
                    (new TextField('Data[FirstName]', '', 'Vorname'))
                        ->ajaxPipelineOnKeyUp([ApiPreviewCertificate::pipelineLoadCertificatePreview(), ApiPreviewCertificate::pipelineLoadDownloadButton()])
                    , 6),
                new FormColumn(
                    (new TextField('Data[LastName]', '', 'Nachname'))
                        ->ajaxPipelineOnKeyUp([ApiPreviewCertificate::pipelineLoadCertificatePreview(), ApiPreviewCertificate::pipelineLoadDownloadButton()])
                    , 6)
            ]),
            new FormRow([
                new FormColumn(
                    (new TextField('Data[Division]', '', 'Klasse'))
                        ->ajaxPipelineOnKeyUp([ApiPreviewCertificate::pipelineLoadCertificatePreview(), ApiPreviewCertificate::pipelineLoadDownloadButton()])
                    , 6),
                new FormColumn(
                    (new TextField('Data[Year]', '', 'Schuljahr'))
                        ->ajaxPipelineOnKeyUp([ApiPreviewCertificate::pipelineLoadCertificatePreview(), ApiPreviewCertificate::pipelineLoadDownloadButton()])
                    , 6)
            ]),
            new FormRow([
                new FormColumn(
                    (new TextField('Data[Birthday]', '', 'Geburtsdatum'))
                        ->ajaxPipelineOnKeyUp([ApiPreviewCertificate::pipelineLoadCertificatePreview(), ApiPreviewCertificate::pipelineLoadDownloadButton()])
                    , 6),
                new FormColumn(
                    (new TextField('Data[Birthplace]', '', 'Geburtsort'))
                        ->ajaxPipelineOnKeyUp([ApiPreviewCertificate::pipelineLoadCertificatePreview(), ApiPreviewCertificate::pipelineLoadDownloadButton()])
                    , 6)
            ]),
            new FormRow([
                new FormColumn(
                    (new TextField('Data[Company]', '', 'Name der Schule'))
                        ->ajaxPipelineOnKeyUp([ApiPreviewCertificate::pipelineLoadCertificatePreview(), ApiPreviewCertificate::pipelineLoadDownloadButton()])
                )
            ]),
            new FormRow([
                new FormColumn(
                    (new TextField('Data[Remark]', '', 'Bemerkungen'))
                        ->ajaxPipelineOnKeyUp([ApiPreviewCertificate::pipelineLoadCertificatePreview(), ApiPreviewCertificate::pipelineLoadDownloadButton()])
                )
            ]),
            new FormRow([
                new FormColumn(
                    (new CheckBox('Data[IsCopy]', 'Zweitschrift anzeigen (Abschluss- und Abgangszeugnisse)', 1))
                        ->ajaxPipelineOnChange([ApiPreviewCertificate::pipelineLoadCertificatePreview(), ApiPreviewCertificate::pipelineLoadDownloadButton()])
                )
            ]),
            new FormRow([
                new FormColumn(
                    (new CheckBox('Data[IsCopyStatement]', 'Zweitschrift mit Namensänderung (mit Beglaubigungsvermerk)', 1))
                        ->ajaxPipelineOnChange([ApiPreviewCertificate::pipelineLoadCertificatePreview(), ApiPreviewCertificate::pipelineLoadDownloadButton()])
                )
            ]),
        ])))->disableSubmitAction();

        $container = (new Container(
            (new Container($form))->setStyle([
                'flex: 1;',
                'background: #E0F0FF;',
                'padding: 10px;',
                'height: 297mm;',
            ])
            . (new Container(ApiPreviewCertificate::receiverContent('', 'CertificatePreview')))
                ->setStyle([
                    'width: 220mm;'
                ])
        ))->setStyle([
            'display: flex;',
            'gap: 10px;',
            'min-height: 310mm'
        ]);

        return $container
            . (new Container('&nbsp;'))->setStyle(['height: 20px;']);
    }

    /**
     * @param $Data
     *
     * @return string
     */
    public function loadDownloadButton($Data): string
    {
        if (!isset($Data['tblCertificate'])
            || !(Generator::useService()->getCertificateById($Data['tblCertificate']))
        ) {
            return '';
        }

        return (new Primary('', '/Api/Education/Certificate/Generator/PreviewTemplate', new Download(), [
            'Data' => $Data
        ], 'Als PDF herunterladen'))
            ->setExternal();
    }

    /**
     * @param $Data
     *
     * @return string
     */
    public function loadCertificatePreview($Data): string
    {
        if (!isset($Data['tblCertificate'])
            || !($tblCertificate = Generator::useService()->getCertificateById($Data['tblCertificate']))
        ) {
            return '';
        }

        $CertificateClass = '\SPHERE\Application\Api\Education\Certificate\Generator\Repository\\' . $tblCertificate->getCertificate();
        if (!class_exists($CertificateClass)) {
            return '';
        }

        $Template = self::getCertificateTemplateForPreview($CertificateClass, $Data);

        $tblPerson = new TblPerson();
        $tblPerson->setId(0);
        $Content = $this->getCertificateContent($tblPerson->getId(), $Data);

        $pageList[$tblPerson->getId()] = $Template->buildPages($tblPerson);
        // Jede Seite einzeln darstellen
        if (!is_array($pageList[$tblPerson->getId()])) {
            $pageList[$tblPerson->getId()] = [$pageList[$tblPerson->getId()]];
        }
        $display = '';
        foreach ($pageList[$tblPerson->getId()] as $Page) {
            $bridge = $Template->createCertificate($Content, [$Page]);
            $display .= (new Container($bridge->getContent()))->setStyle([
                'width: 210mm;',
                'height: 297mm;',
//                'width: 794px;',
//                'height: 1123px;',
                'padding: 15mm;',
                'background: white;',
                'border: 1px solid #aaa;',
                'box-shadow: 0 0 5px rgba(0,0,0,0.3);',
                'box-sizing: border-box;',
            ]);
        }

        return $display;
    }

    /**
     * @param string $CertificateClass
     * @param $Data
     *
     * @return Certificate
     */
    public static function getCertificateTemplateForPreview(string $CertificateClass, $Data): Certificate
    {
        // Zweitschrift anzeigen (Abschluss- und Abgangszeugnisse)
        if (isset($Data['IsCopy']) || isset($Data['IsCopyStatement'])) {
            $CopyCertificateData = [
                'Leader' => 'Schmitt',
                'HeadmasterOriginalName' => 'Meyer',
                'FirstMember' => 'Schulze',
                'SecondMember' => 'Fleischer',
                'DivisionTeacherOriginalName' => 'Weber',
                'SealText' => 'Schulzentrum Leinefelde Original Siegel',
                'Date' => (new DateTime('today'))->format('d.m.Y'),
                'City' => 'Zwickau',
            ];

            if (isset($Data['IsCopyStatement'])) {
                $CopyCertificateData['IsCopyStatement'] = true;
            }

            /** @var Certificate $Template */
            $Template = new $CertificateClass(null, null, false, [], $CopyCertificateData);
        } else {
            /** @var Certificate $Template */
            $Template = new $CertificateClass();
        }

        return $Template;
    }

    /**
     * @param int $personId
     * @param $Data
     *
     * @return array
     */
    public function getCertificateContent(int $personId, $Data): array
    {
        $Content = [];
        // Person data
        $Content['P' . $personId]['Person']['Id'] = $personId;
        $Content['P' . $personId]['Person']['Data']['Name']['First'] = $Data['FirstName'] ?? '';
        $Content['P' . $personId]['Person']['Data']['Name']['Last'] = $Data['LastName'] ?? '';
        $Content['P' . $personId]['Division']['Data']['Name'] = $Data['Division'] ?? '';
        $Content['P' . $personId]['Division']['Data']['Year'] = $Data['Year'] ?? '';
        $Content['P' . $personId]['Person']['Common']['BirthDates']['Birthday'] = $Data['Birthday'] ?? '';
        $Content['P' . $personId]['Person']['Common']['BirthDates']['Birthplace'] = $Data['Birthplace'] ?? '';
        $Content['P' . $personId]['Company']['Data']['Name'] = $Data['Company'] ?? '';
        $Content['P' . $personId]['Input']['Remark'] = $Data['Remark'] ?? '';

        $this->setBehaviorGrade($Content, $personId, 'KBE', '1');
        $this->setBehaviorGrade($Content, $personId, 'KFL', '3');
        $this->setBehaviorGrade($Content, $personId, 'KMI', '2');
        $this->setBehaviorGrade($Content, $personId, 'KOR', '4');

        $this->setSubjectGrade($Content, $personId, 'DE', '2');
        $this->setSubjectGrade($Content, $personId, 'EN', 'teilgenommen', true);
        $this->setSubjectGrade($Content, $personId, 'KU', '1');
        $this->setSubjectGrade($Content, $personId, 'MU', '3');
        $this->setSubjectGrade($Content, $personId, 'MA', '1');
        $this->setSubjectGrade($Content, $personId, 'SPO', '2');

        return $Content;
    }

    /**
     * @param array $Content
     * @param int $personId
     * @param string $subjectAcronym
     * @param string $grade
     * @param bool $isGradeText
     *
     * @return void
     */
    private function setSubjectGrade(array &$Content, int $personId, string $subjectAcronym, string $grade, bool $isGradeText = false): void
    {
        if (($tblSubject = Subject::useService()->getSubjectByVariantAcronym($subjectAcronym))) {
            $Content['P' . $personId]['Grade']['Data'][$tblSubject->getAcronym()] = $grade;

            if ($isGradeText) {
                $Content['P' . $personId]['Grade']['Data']['IsShrinkSize'][$tblSubject->getAcronym()] = true;
            }
        }
    }

    /**
     * @param array $Content
     * @param int $personId
     * @param string $gradeTypeCode
     * @param string $grade
     *
     * @return void
     */
    private function setBehaviorGrade(array &$Content, int $personId, string $gradeTypeCode, string $grade): void
    {
        if (($tblGradeType = Grade::useService()->getGradeTypeByCode($gradeTypeCode))) {
            $Content['P' . $personId]['Input'][$tblGradeType->getCode()] = $grade;
        }
    }
}