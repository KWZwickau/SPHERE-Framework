<?php

namespace SPHERE\Application\Education\Graduation\Gradebook;

use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;

/**
 * Class Frontend
 * @package SPHERE\Application\Education\Graduation\Gradebook
 */
class Frontend
{

    /**
     * @return Stage
     */
    public function frontendGradeType()
    {

        $Stage = new Stage('Notenbuch', 'Zensuren-Typen');
        $Stage->addButton(
            new Standard('Zensuren-Typ anlegen', '/Education/Graduation/Gradebook/GradeType/Create', new Plus())
        );

        $tblGradeType = Gradebook::useService()->getGradeTypeAll();

        $Stage->setContent(
            new Form(array(
                new FormGroup(array(
                    new FormRow(array(
                        new FormColumn(array(
                            new TableData($tblGradeType, null, array(
                                'Name' => 'Name',
                                'Code' => 'Abk&uuml;rzung',
                                'Description' => 'Beschreibung',
                                //'Option' => 'Option'
                            ))
                        ))
                    ))
                ))
            ))
        );

        return $Stage;
    }

    /**
     * @param $GradeType
     * @return Stage
     */
    public function frontendCreateGradeType($GradeType)
    {
        $Stage = new Stage('Noten Administration', 'Zensuren-Typ anlegen');
        $Stage->addButton(
            new Standard('Zur&uuml;ck', '/Education/Graduation/Gradebook', new ChevronLeft())
        );

        $Form = $this->formGradeType()
            ->appendFormButton(new Primary('Anlegen'))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
        $Stage->setContent(Gradebook::useService()->createGradeType($Form, $GradeType));

        return $Stage;
    }

    private function formGradeType()
    {
        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new TextField('GradeType[Name]', 'Leistungskontrolle', 'Name'), 9
                ),
                new FormColumn(
                    new TextField('GradeType[Code]', 'LK', 'Abk&uuml;rzung'), 3
                )
            )),
            new FormRow(array(
                new FormColumn(
                    new TextField('GradeType[Description]', '', 'Beschreibung'), 9
                ),
                new FormColumn(
                    new CheckBox('GradeType[IsHighlighted]', 'Fett markiert', 1), 3
                )
            ))
        )));
    }
}