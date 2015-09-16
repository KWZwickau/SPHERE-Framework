<?php

namespace SPHERE\Application\Education\Graduation\ScoreType;

use SPHERE\Application\Education\Graduation\ScoreType\Service\Entity\TblScoreType;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Graduation\Score\ScoreType
 */
class Frontend
{

    /**
     * @param $ScoreType
     *
     * @return Stage
     */
    public function frontendScoreType($ScoreType)
    {

        $Stage = new Stage('Zensuren Typen');

        $tblScoreType = ScoreType::useService()->getScoreTypeAll();

        /** @var TblScoreType $ScoreTyp */
        foreach ($tblScoreType as $ScoreTyp) {
            $ScoreTyp->Option = (new \SPHERE\Common\Frontend\Link\Repository\Primary(
                'Löschen', '/Education/Graduation/ScoreType/Remove', new Remove(),
                array('Id' => $ScoreTyp->getId())))->__toString();
        }

        $Stage->setContent(
            new Form(array(
                new FormGroup(array(
                    new FormRow(array(
                        new FormColumn(array(
                            new TableData($tblScoreType, null, array(
                                'Id'     => 'Identifer',
                                'Name'   => 'Zensurentyp',
                                'Short'  => 'Abkürzung',
                                'Option' => 'Option'
                            ))
                        ))
                    ))
                ))
            )).
            ScoreType::useService()->setScoreType(
                new Form(array(
                    new FormGroup(array(
                        new FormRow(array(
                            new FormColumn(
                                new TextField('ScoreType[Name]', 'Leistungskontrolle', 'Zensurentyp'), 4
                            ),
                            new FormColumn(
                                new TextField('ScoreType[Short]', 'LK', 'Kurz'), 4
                            )
                        ))
                    ))
                ), new Primary('Hinzufügen', new Plus())),
                $ScoreType)
        );

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendScoreTypeRemove($Id)
    {

        $Stage = new Stage('Zensurtyp', 'Entfernen');

        if (( $ScoreType = ScoreType::useService()->getScoreTypeById($Id) )) {
            $Stage->setContent(ScoreType::useService()->removeScoreType($ScoreType));
        } else {
            $Stage->setContent(new Danger('Der Zensurentyp konnte nicht gelöscht werden')
                .new Redirect('/Education/Graduation/ScoreType', 2));
        }
        return $Stage;
    }
}
