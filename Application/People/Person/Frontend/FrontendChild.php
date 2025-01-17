<?php

namespace SPHERE\Application\People\Person\Frontend;

use SPHERE\Application\Api\People\Person\ApiPersonEdit;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Child\Child;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\TemplateReadOnly;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
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
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Success;

/**
 * Class FrontendChild
 * 
 * @package SPHERE\Application\People\Person\Frontend
 */
class FrontendChild extends FrontendReadOnly
{
    const TITLE = 'Abholberechtigte';

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public static function getChildContent($PersonId = null)
    {
        if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
            // Block Abholberechtigte nur bei Gruppe Schüller + Mandanteneinstellung (Gruppen komma getrennt)
            if (($tblSetting = Consumer::useService()->getSetting('People', 'Meta', 'Child', 'AuthorizedToCollectGroups'))
                && ($value = $tblSetting->getValue())
            ) {
                $AuthorizedToCollectGroups = explode(',', $value);
            } else {
                $AuthorizedToCollectGroups = array();
            }
            $AuthorizedToCollectGroups[] = 'Schüler';
            $hasBlockChild = false;
            foreach ($AuthorizedToCollectGroups as $group) {
                if (($tblGroup = Group::useService()->getGroupByName(trim($group)))
                    && Group::useService()->existsGroupPerson($tblGroup, $tblPerson)
                ) {
                    $hasBlockChild = true;
                    break;
                }
            }

            if ($hasBlockChild) {
                if (($tblChild = $tblPerson->getChild())) {
                    $AuthorizedToCollect = $tblChild->getAuthorizedToCollect();
                } else {
                    $AuthorizedToCollect = '';
                }

                $content = new Layout(new LayoutGroup(array(
                    new LayoutRow(array(
                        self::getLayoutColumnLabel('Abholberechtigte'),
                        self::getLayoutColumnValue($AuthorizedToCollect, 10),
                    )),
                )));

                $editLink = (new Link(new Edit() . ' Bearbeiten', ApiPersonEdit::getEndpoint()))
                    ->ajaxPipelineOnClick(ApiPersonEdit::pipelineEditChildContent($PersonId));
                $DivisionString = FrontendReadOnly::getDivisionString($tblPerson);

                return TemplateReadOnly::getContent(
                    self::TITLE,
                    self::getSubContent('', $content),
                    array($editLink),
                    'der Person ' . new Bold(new Success($tblPerson->getFullName())).$DivisionString,
                    new Tag()
                );
            }
        }

        return '';
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function getEditChildContent($PersonId = null)
    {
        $tblPerson = false;
        if ($PersonId && ($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $Global = $this->getGlobal();

            if (($tblChild = Child::useService()->getChildByPerson($tblPerson))) {
                $Global->POST['Meta']['AuthorizedToCollect'] = $tblChild->getAuthorizedToCollect();
                if($tblChild->getAuthorizedToCollect()){
                    $Global->POST['Meta']['CheckAuthorizedToCollect'] = true;
                }

                $Global->savePost();
            }
        }

        return $this->getEditChildTitle($tblPerson ? $tblPerson : null)
            . new Well($this->getEditChildForm($tblPerson ? $tblPerson : null));
    }

    /**
     * @param TblPerson|null $tblPerson
     * @param bool $isCreatePerson
     *
     * @return Title|string
     */
    public function getEditChildTitle(TblPerson $tblPerson = null, $isCreatePerson = false)
    {
        $DivisionString = FrontendReadOnly::getDivisionString($tblPerson);
        $title = new Title(new Tag() . ' ' . self::TITLE, 'der Person '
            . ($tblPerson ? new Bold(new Success($tblPerson->getFullName())) : '').$DivisionString
            . ($isCreatePerson ? ' anlegen' : ' bearbeiten'));
        if ($isCreatePerson) {
            return $title;
        } else {
            return $title . self::getDataProtectionMessage();
        }
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Form
     */
    public function getEditChildForm(TblPerson $tblPerson = null)
    {
        return new Form(array(
            new FormGroup(array(
                $this->getChildFormRow(),
                new FormRow(array(
                    new FormColumn(array(
                        (new Primary('Speichern', ApiPersonEdit::getEndpoint(), new Save()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineSaveChildContent($tblPerson ? $tblPerson->getId() : 0)),
                        (new Primary('Abbrechen', ApiPersonEdit::getEndpoint(), new Disable()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineCancelChildContent($tblPerson ? $tblPerson->getId() : 0))
                    ))
                ))
            ))
        ));
    }

    /**
     * @param TblPerson|null $tblPerson
     * @param $Meta
     *
     * @return bool|string
     */
    public function checkInputChildContent(TblPerson $tblPerson = null, $Meta = array())
    {
        $error = false;
        $form = $this->getEditChildForm($tblPerson ? $tblPerson : null);
        if (isset($Meta['AuthorizedToCollect'] ) && !empty($Meta['AuthorizedToCollect'])) {
            if (!isset($Meta['CheckAuthorizedToCollect'])){
                $form->setError('Meta[AuthorizedToCollect]', 'Eingabe erfordert Vollmacht');
                $error = true;
            }
        }

        if ($error) {
            return $this->getEditChildTitle($tblPerson ? $tblPerson : null)
                . new Well($form);
        }
        return false;
    }

    /**
     * @return FormRow
     */
    public function getChildFormRow()
    {
        return new FormRow(array(
            new FormColumn(array(
                new Panel('', array(
                    (new TextArea('Meta[AuthorizedToCollect]', '', 'Abholberechtigte'
                        . (new CheckBox('Meta[CheckAuthorizedToCollect]', 'schriftliche Vollmacht liegt vor', 1)), null, 2)),
                ), Panel::PANEL_TYPE_INFO)
            )),
        ));
    }
}