<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.12.2018
 * Time: 10:08
 */

namespace SPHERE\Application\People\Person\Frontend;

use SPHERE\Application\Api\People\Person\ApiPersonEdit;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Club\Club;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\TemplateReadOnly;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
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
 * Class FrontendClub
 *
 * @package SPHERE\Application\People\Person\Frontend
 */
class FrontendClub  extends FrontendReadOnly
{
    const TITLE = 'Vereinsmitglied-Daten';

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public static function getClubContent($PersonId = null)
    {
        if (($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblGroup = Group::useService()->getGroupByMetaTable('CLUB'))
            && Group::useService()->existsGroupPerson($tblGroup, $tblPerson)
        ) {
            if (($tblClub = Club::useService()->getClubByPerson($tblPerson))) {
                $identifier = $tblClub->getIdentifier();
                $entryDate = $tblClub->getEntryDate();
                $exitDate = $tblClub->getExitDate();
                $remark = $tblClub->getRemark();
            } else {
                $identifier = '';
                $entryDate = '';
                $exitDate = '';
                $remark = '';
            }

            $content = new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    self::getLayoutColumnLabel('Mitgliedsnummer'),
                    self::getLayoutColumnValue($identifier),
                    self::getLayoutColumnLabel('Eintrittsdatum'),
                    self::getLayoutColumnValue($entryDate),
                    self::getLayoutColumnLabel('Austrittsdatum'),
                    self::getLayoutColumnValue($exitDate),
                )),
                new LayoutRow(array(
                    self::getLayoutColumnLabel('Bemerkungen'),
                    self::getLayoutColumnValue($remark, 10),
                )),
            )));

            $editLink = (new Link(new Edit() . ' Bearbeiten', ApiPersonEdit::getEndpoint()))
                ->ajaxPipelineOnClick(ApiPersonEdit::pipelineEditClubContent($PersonId));

            return TemplateReadOnly::getContent(
                self::TITLE,
                $content,
                array($editLink),
                'der Person' . new Bold(new Success($tblPerson->getFullName())),
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
    public function getEditClubContent($PersonId = null)
    {

        $tblPerson = false;
        if ($PersonId && ($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $Global = $this->getGlobal();

            if (($tblClub = Club::useService()->getClubByPerson($tblPerson))) {
                $Global->POST['Meta']['Identifier'] = $tblClub->getIdentifier();
                $Global->POST['Meta']['EntryDate'] = $tblClub->getEntryDate();
                $Global->POST['Meta']['ExitDate'] = $tblClub->getExitDate();
                $Global->POST['Meta']['Remark'] = $tblClub->getRemark();
                $Global->savePost();
            }
        }

        return $this->getEditClubTitle($tblPerson ? $tblPerson : null)
            . new Well($this->getEditClubForm($tblPerson ? $tblPerson : null));
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return string
     */
    private function getEditClubTitle(TblPerson $tblPerson = null)
    {
        return new Title(new Tag() . ' ' . self::TITLE, 'der Person'
                . ($tblPerson ? new Bold(new Success($tblPerson->getFullName())) : '') . ' bearbeiten')
            . self::getDataProtectionMessage();
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Form
     */
    private function getEditClubForm(TblPerson $tblPerson = null)
    {

        return (new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Mitglied', array(
                            new TextField(
                                'Meta[Identifier]', 'Mitgliedsnummer', 'Mitgliedsnummer'
                            ),
                        ), Panel::PANEL_TYPE_INFO
                        ), 4),
                    new FormColumn(
                        new Panel('Daten', array(
                            new DatePicker(
                                'Meta[EntryDate]', '', 'Eintrittsdatum', new Calendar()
                            ),
                            new DatePicker(
                                'Meta[ExitDate]', '', 'Austrittsdatum', new Calendar()
                            ),
                        ), Panel::PANEL_TYPE_INFO
                        ), 4),
                    new FormColumn(
                        new Panel('Sonstiges', array(
                            new TextArea('Meta[Remark]', 'Bemerkungen', 'Bemerkungen', new Pencil())
                        ), Panel::PANEL_TYPE_INFO
                        ), 4),
                )),
                new FormRow(array(
                    new FormColumn(array(
                        (new Primary('Speichern', ApiPersonEdit::getEndpoint(), new Save()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineSaveClubContent($tblPerson ? $tblPerson->getId() : 0)),
                        (new Primary('Abbrechen', ApiPersonEdit::getEndpoint(), new Disable()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineCancelClubContent($tblPerson ? $tblPerson->getId() : 0))
                    ))
                ))
            ))
        )))->disableSubmitAction();
    }
}