<?php
namespace SPHERE\System\Database\Filter\Preparation;

class Person extends AbstractPreparation
{

    /**
     * Person constructor.
     */
    public function __construct($Id)
    {

        $Entity = \SPHERE\Application\People\Person\Person::useService()->getPersonById($Id);

//        $Properties
    }


}
