<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.12.2018
 * Time: 09:04
 */

namespace SPHERE\Application\People\Person\Frontend;

use SPHERE\Application\Api\People\Person\ApiPersonEdit;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\TemplateReadOnly;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Tag;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Link\Repository\Link;

/**
 * Class FrontendTeacher
 *
 * @package SPHERE\Application\People\Person\Frontend
 */
class FrontendTeacher extends FrontendReadOnly
{
    const TITLE = 'Lehrer-Daten';

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public static function getTeacherContent($PersonId = null)
    {
        if (($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblGroup = Group::useService()->getGroupByMetaTable('TEACHER'))
            && Group::useService()->existsGroupPerson($tblGroup, $tblPerson)
        ) {
            if (($tblTeacher = Teacher::useService()->getTeacherByPerson($tblPerson))) {
                $acronym = $tblTeacher->getAcronym();
            } else {
                $acronym = '';
            }

            $content = new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    self::getLayoutColumnLabel('Kürzel'),
                    self::getLayoutColumnValue($acronym),
                    self::getLayoutColumnEmpty(8),
                )),
            )));

            $editLink = (new Link(new Edit() . ' Bearbeiten', ApiPersonEdit::getEndpoint()))
                ->ajaxPipelineOnClick(ApiPersonEdit::pipelineEditTeacherContent($PersonId));

            return TemplateReadOnly::getContent(
                self::TITLE,
                $content,
                array($editLink),
                'der Person ' . new Bold(new Success($tblPerson->getFullName())),
                new Tag()
            );
        }

        return '';
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function getEditTeacherContent($PersonId = null)
    {

        $tblPerson = false;
        if ($PersonId && ($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $Global = $this->getGlobal();

            if (($tblTeacher = Teacher::useService()->getTeacherByPerson($tblPerson))) {
                $Global->POST['Meta']['Acronym'] = $tblTeacher->getAcronym();
                $Global->savePost();
            }
        }

        return $this->getEditTeacherTitle($tblPerson ? $tblPerson : null)
            . new Well($this->getEditTeacherForm($tblPerson ? $tblPerson : null));
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return string
     */
    private function getEditTeacherTitle(TblPerson $tblPerson = null)
    {
        return new Title(new Tag() . ' ' . self::TITLE, self::getEditTitleDescription($tblPerson))
            . self::getDataProtectionMessage();
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Form
     */
    private function getEditTeacherForm(TblPerson $tblPerson = null)
    {

        return (new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Lehrer', array(
                            new TextField(
                                'Meta[Acronym]', 'Kürzel', 'Kürzel'
                            ),
                        ), Panel::PANEL_TYPE_INFO
                        ), 12),
                )),
                new FormRow(array(
                    new FormColumn(array(
                        (new Primary('Speichern', ApiPersonEdit::getEndpoint(), new Save()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineSaveTeacherContent($tblPerson ? $tblPerson->getId() : 0)),
                        (new Primary('Abbrechen', ApiPersonEdit::getEndpoint(), new Disable()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineCancelTeacherContent($tblPerson ? $tblPerson->getId() : 0))
                    ))
                ))
            ))
        )))->disableSubmitAction();
    }

    /**
     * @param TblPerson|null $tblPerson
     * @param $Meta
     *
     * @return bool|string
     */
    public function checkInputTeacherContent(TblPerson $tblPerson = null, $Meta)
    {
        $error = false;
        $form = $this->getEditTeacherForm($tblPerson ? $tblPerson : null);
        if (isset($Meta['Acronym']) && !empty($Meta['Acronym'])) {
            if (($tblTeacher = Teacher::useService()->getTeacherByAcronym($Meta['Acronym']))
                && ($tblTeacherPerson = $tblTeacher->getServiceTblPerson())
                && $tblTeacherPerson->getId() != $tblPerson->getId()
            ) {
                $form->setError('Meta[Acronym]', 'Dieses Kürzel wird bereits verwendet');
                $error = true;
            }
        }

        if ($error) {
            return $this->getEditTeacherTitle($tblPerson ? $tblPerson : null)
                . new Well($form);
        }

        return $error;
    }
}