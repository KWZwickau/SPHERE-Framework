<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.12.2018
 * Time: 09:43
 */

namespace SPHERE\Application\People\Person\Frontend;

use SPHERE\Application\Api\People\Person\ApiPersonEdit;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Custody\Custody;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\TemplateReadOnly;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\MapMarker;
use SPHERE\Common\Frontend\Icon\Repository\Nameplate;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
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
 * Class FrontendCustody
 * 
 * @package SPHERE\Application\People\Person\Frontend
 */
class FrontendCustody  extends FrontendReadOnly
{
    const TITLE = 'Sorgerecht-Daten';

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public static function getCustodyContent($PersonId = null)
    {
        if (($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblGroup = Group::useService()->getGroupByMetaTable('CUSTODY'))
            && Group::useService()->existsGroupPerson($tblGroup, $tblPerson)
        ) {
            if (($tblCustody = Custody::useService()->getCustodyByPerson($tblPerson))) {
                $occupation = $tblCustody->getOccupation();
                $employment = $tblCustody->getEmployment();
                $remark = $tblCustody->getRemark();
            } else {
                $occupation = '';
                $employment = '';
                $remark = '';
            }

            $content = new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    self::getLayoutColumnLabel('Beruf'),
                    self::getLayoutColumnValue($occupation),
                    self::getLayoutColumnLabel('Arbeitsstelle'),
                    self::getLayoutColumnValue($employment),
                    self::getLayoutColumnEmpty(4),
                )),
                new LayoutRow(array(
                    self::getLayoutColumnLabel('Bemerkungen'),
                    self::getLayoutColumnValue($remark, 10),
                )),
            )));

            $editLink = (new Link(new Edit() . ' Bearbeiten', ApiPersonEdit::getEndpoint()))
                ->ajaxPipelineOnClick(ApiPersonEdit::pipelineEditCustodyContent($PersonId));

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
    public function getEditCustodyContent($PersonId = null)
    {

        $tblPerson = false;
        if ($PersonId && ($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $Global = $this->getGlobal();

            if ($tblCustody = Custody::useService()->getCustodyByPerson($tblPerson)) {
                $Global->POST['Meta']['Remark'] = $tblCustody->getRemark();
                $Global->POST['Meta']['Occupation'] = $tblCustody->getOccupation();
                $Global->POST['Meta']['Employment'] = $tblCustody->getEmployment();
                $Global->savePost();
            }
        }

        return $this->getEditCustodyTitle($tblPerson ? $tblPerson : null)
            . new Well($this->getEditCustodyForm($tblPerson ? $tblPerson : null));
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return string
     */
    private function getEditCustodyTitle(TblPerson $tblPerson = null)
    {
        return new Title(new Tag() . ' ' . self::TITLE, self::getEditTitleDescription($tblPerson))
            . self::getDataProtectionMessage();
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Form
     */
    private function getEditCustodyForm(TblPerson $tblPerson = null)
    {

        return (new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Berufliches', array(
                            new AutoCompleter('Meta[Occupation]', 'Beruf', 'Beruf',
                                array(), new MapMarker()
                            ),
                            new AutoCompleter('Meta[Employment]', 'Arbeitsstelle', 'Arbeitsstelle',
                                array(), new Nameplate()
                            ),
                        ), Panel::PANEL_TYPE_INFO
                        ), 6),
                    new FormColumn(
                        new Panel('Sonstiges', array(
                            new TextArea('Meta[Remark]', 'Bemerkungen', 'Bemerkungen', new Pencil())
                        ), Panel::PANEL_TYPE_INFO
                        ), 6),
                )),
                new FormRow(array(
                    new FormColumn(array(
                        (new Primary('Speichern', ApiPersonEdit::getEndpoint(), new Save()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineSaveCustodyContent($tblPerson ? $tblPerson->getId() : 0)),
                        (new Primary('Abbrechen', ApiPersonEdit::getEndpoint(), new Disable()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineCancelCustodyContent($tblPerson ? $tblPerson->getId() : 0))
                    ))
                ))
            ))
        )))->disableSubmitAction();
    }
}