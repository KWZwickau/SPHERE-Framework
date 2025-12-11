<?php

namespace SPHERE\Application\Education\Certificate\Setting;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Api\Education\Certificate\Setting\ApiPreviewCertificate;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class FrontendPreviewCertificate extends Extension implements IFrontendInterface
{
    public function frontend(): Stage
    {
        $stage = new Stage('Zeugnisvorlagen Vorschau');
        $stage = Frontend::setSettingMenue($stage, 'Preview');

        if (!($tblConsumer = Consumer::useService()->getConsumerBySession())) {
            return $stage;
        }

        if ($tblConsumer->getAcronym() == 'REF') {
            // TODO oder doch besser erst Consumer auswählen
            $tblCertificateList = Generator::useService()->getCertificateAll();
        } else {
            $tblCertificateList = Generator::useService()->getTemplateAllByConsumer(null);
        }

        if (!$tblCertificateList) {
            $tblCertificateList = [];
        }
        if (($tblCertificatesConsumer = Generator::useService()->getTemplateAllByConsumer($tblConsumer))) {
            $tblCertificateList = array_merge($tblCertificatesConsumer, $tblCertificateList);
        }

        $selectList = [];
        /** @var TblCertificate $tblCertificate */
        foreach ($tblCertificateList as $tblCertificate) {
            if (($tblSchoolType = $tblCertificate->getServiceTblSchoolType())) {
                $selectList[$tblCertificate->getId()] =
                    ($tblSchoolType->getShortName() ?: $tblSchoolType->getName())
                    . ' - ' . $tblCertificate->getName()
                    . (($description = $tblCertificate->getDescription()) ? ' - ' . $description : '')
                    . (($tblConsumerCertificate = $tblCertificate->getServiceTblConsumer(true)) ? ' - ' . $tblConsumerCertificate->getAcronym() : '')
                ;
            }
        }

        $form = (new Form(new FormGroup([
            new FormRow([
                new FormColumn(
                    (new SelectBox('Data[tblCertificate]', 'Zeugnisvorlage', $selectList))
                        ->ajaxPipelineOnChange(ApiPreviewCertificate::pipelineLoadContent()),
                )
            ])
        ])))->disableSubmitAction();

        $container = (new Container(
            (new Container($form))->setStyle([
                'flex: 1;',
                'background: #eef;',
                'padding: 10px;'
            ])
            . (new Container(ApiPreviewCertificate::receiverContent('', 'Content')))
                ->setStyle([
                    'width: 220mm;'
                ])
        ))->setStyle([
            'display: flex;',
            'gap: 10px;',
        ]);

        $stage->setContent(
            $container
            . (new Container('&nbsp;'))->setStyle(['height: 20px;'])
        );

        return $stage;
    }

    /**
     * @param $Data
     *
     * @return string
     */
    public function loadContent($Data): string
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

        // TODO: aktualisieren Button

        // TODO: pdf Download

//        $tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear);
//        $tblDivisionCourse = $tblPrepare->getServiceTblDivision();
        /** @var Certificate $Template */
        $Template = new $CertificateClass();

//        $tblPerson = Person::useService()->getPersonById(301);
//        $personId = 301;
        $tblPerson = new TblPerson();
        $tblPerson->setId(-1);
        $personId = $tblPerson->getId();

        // get Content
//        $Content = Prepare::useService()->createCertificateContent($tblPerson, $tblPrepareStudent);
        $Content = [];
        // Person data
        $Content['P' . $personId]['Person']['Id'] = $personId;
        $Content['P' . $personId]['Person']['Data']['Name']['First'] = 'Maximilian';
        $Content['P' . $personId]['Person']['Data']['Name']['Last'] = 'Mustermann';
        // TODO content direct hier setzen oder aus Data lesen

        if (isset($Content['P' . $personId]['Grade'])) {
            $Template->setGrade($Content['P' . $personId]['Grade']);
        }
        if (isset($Content['P' . $personId]['AdditionalGrade'])) {
            $Template->setAdditionalGrade($Content['P' . $personId]['AdditionalGrade']);
        }

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
}