<?php

namespace SPHERE\Application\Education\ClassRegister\Digital\Frontend;

use SPHERE\Application\Api\Education\ClassRegister\ApiMail;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblType;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Envelope;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Extern;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;

class FrontendMail extends FrontendForgotten
{
    /**
     * @param null $DivisionCourseId
     * @param null $BackDivisionCourseId
     * @param string $BasicRoute
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function frontendMail(
        $DivisionCourseId = null,
        $BackDivisionCourseId = null,
        string $BasicRoute = '/Education/ClassRegister/Digital/Teacher'
    ): string {
        $icon = new Envelope();
        $name = 'E-Mail-Kontakt';
        $Route = '/Education/ClassRegister/Digital/Mail';
        $content = new Bold('Hinweis: ') . 'Für jede Person wird immer nur die neueste E-Mail-Adresse hinzugefügt.'
            . new Container('&nbsp;')
            . $this->getMailSelect($DivisionCourseId)
            . ApiMail::receiverBlock($this->loadMailContent($DivisionCourseId, null), 'MailContent');

        return Digital::useFrontend()->getStage($DivisionCourseId, $BasicRoute, $Route, $icon, $name, $content, $BackDivisionCourseId);
    }

    /**
     * @param $DivisionCourseId
     *
     * @return string
     */
    private function getMailSelect($DivisionCourseId): string
    {
        return new Panel(
            new Select() . ' Auswahl',
            new Form(new FormGroup([
                new FormRow([
                    new FormColumn(
                        (new SelectBox('Data[TypeId]', 'E-Mail Typ', ['{{ Name }}' => Mail::useService()->getTypeAll()]))
                            ->setRequired()
                            ->ajaxPipelineOnChange(ApiMail::pipelineLoadMailContent($DivisionCourseId))
                    )
                ]),
                new FormRow([
                    new FormColumn(
                        (new CheckBox('Data[Custody]', new Bold('Sorgeberechtigte'), 1))
                            ->ajaxPipelineOnChange(ApiMail::pipelineLoadMailContent($DivisionCourseId))
                    , 3),
                    new FormColumn(
                        (new CheckBox('Data[Student]', new Bold('Schüler'), 1))
                            ->ajaxPipelineOnChange(ApiMail::pipelineLoadMailContent($DivisionCourseId))
                    , 3)
                ]),
            ])),
            Panel::PANEL_TYPE_INFO
        );
    }

    /**
     * @param $DivisionCourseId
     * @param $Data
     *
     * @return string
     */
    public function loadMailContent($DivisionCourseId, $Data): string
    {
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && isset($Data['TypeId'])
            && ($tblTypeMail = Mail::useService()->getTypeById($Data['TypeId']))
            && (isset($Data['Custody']) || isset($Data['Student']))
        ) {
            $missingCustodyList = [];
            $missingStudentList = [];
            $mailList = [];
            if (($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())
                && ($tblTypeGuardian = Relationship::useService()->getTypeByName(TblType::IDENTIFIER_GUARDIAN))
            ) {
                foreach ($tblPersonList as $tblPerson) {
                    if (isset($Data['Student'])) {
                        if ($tblMail = Mail::useService()->getLastMailAddressByPersonAndType($tblPerson, $tblTypeMail)) {
                            $mailList[] = $tblMail->getAddress();
                        } else {
                            $missingStudentList[] = $tblPerson->getFullName();
                        }
                    }

                    if (isset($Data['Custody'])) {
                        if (($tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblTypeGuardian))) {
                            foreach ($tblRelationshipList as $tblRelationship) {
                                if (($tblPersonFrom = $tblRelationship->getServiceTblPersonFrom())) {
                                    if ($tblMail = Mail::useService()->getLastMailAddressByPersonAndType($tblPersonFrom, $tblTypeMail)) {
                                        $mailList[] = $tblMail->getAddress();
                                    } else {
                                        $missingCustodyList[] = $tblPersonFrom->getFullName();
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $panelCustody = '';
            if ($missingCustodyList) {
                $panelCustody = new Panel(
                    'Fehlende E-Mail-Adressen für folgende Sorgeberechtigte',
                    $missingCustodyList,
                    Panel::PANEL_TYPE_WARNING
                );
            }
            $panelStudent = '';
            if ($missingStudentList) {
                $panelStudent = new Panel(
                    'Fehlende E-Mail-Adressen für folgende Schüler',
                    $missingStudentList,
                    Panel::PANEL_TYPE_WARNING
                );
            }
            $layout = '';
            if ($panelCustody || $panelStudent) {
                $layout = new Layout(new LayoutGroup(new LayoutRow([
                    $panelCustody ? new LayoutColumn($panelCustody, 6) : null,
                    $panelStudent ? new LayoutColumn($panelStudent, 6) : null
                ])));
            }

            if ($mailList) {
                $mails = implode('; ', $mailList);

                return $layout .
                    new Primary('Im E-Mail-Programm als BCC öffnen', 'mailto:;?bcc=' . $mails, new Extern())
                    . new Title('Oder zum Kopieren ins BCC-Feld')
                    . new Well($mails)
                    ;
            } else {
                return $layout . new Warning('Keine entsprechenden E-Mail-Adressen vorhanden!' , new Ban());
            }
        }

        return new Warning('Bitte wählen Sie zunächst einen E-Mail-Typ und mindestens eine Zielgruppe aus.' , new Exclamation());
    }
}