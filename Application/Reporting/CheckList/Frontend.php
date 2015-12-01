<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 01.12.2015
 * Time: 10:33
 */

namespace SPHERE\Application\Reporting\CheckList;


use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;

class Frontend
{
    /**
     * @param null $List
     * @return Stage
     */
    public function frontendList($List = null)
    {
        $Stage = new Stage('Check-Listen', 'Übersicht');

        $tblListAll = CheckList::useService()->getListAll();

        if ($tblListAll){
            foreach ($tblListAll as &$tblList){
                $tblList->Type = $tblList->getTblListType()->getName();
            }
        }

        $Form = $this->formList()
            ->appendFormButton(new Primary('Hinzufügen', new Plus()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($tblListAll, null, array(
                                'Name' => 'Name',
                                'Description' => 'Beschreibung',
                                'Type' => 'Typ',
                                'Option' => 'Optionen',
                            ))
                        ))
                    ))
                ), new Title('Übersicht')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            CheckList::useService()->createList($Form, $List)
                        ))
                    ))
                ), new Title('Hinzufügen'))
            ))
        );

        return $Stage;
    }

    private function formList()
    {
        $tblListTypeAll = CheckList::useService()->getListTypeAll();

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new TextField('List[Name]', 'Name', 'Name'), 6
                ),
                new FormColumn(
                    new SelectBox('List[Type]', 'Typ', array('{{ Name }}' => $tblListTypeAll )), 6
                ),
                new FormColumn(
                    new TextField('List[Description]', 'Beschreibung', 'Beschreibung'), 12
                )
            ))
        )));
    }
}