<?php

namespace SPHERE\Application\RestApi\Person;

use SPHERE\Application\People\Person\Person;
use SPHERE\Application\RestApi\IApiInterface;
use SPHERE\Common\Main;
use stdClass;

class ApiPerson implements IApiInterface
{
    public static function registerApi(): void
    {
        Main::getRestApiDispatcher()->registerRoute(Main::getRestApiDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__ . '::getPerson',
        ));
        Main::getRestApiDispatcher()->registerRoute(Main::getRestApiDispatcher()->createRoute(
            __NAMESPACE__ . '/MainAddress', __CLASS__ . '::getMainAddress',
        ));
    }

    public static function getPerson($PersonId = null)
    {
        if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $obj = new stdClass();
            $obj->Id = $tblPerson->getId();
            $obj->FullName = $tblPerson->getFullName();

//            return json_encode((array)$tblPerson);
//            return json_encode((array)$obj);
            return $obj;
        }

        return null;
    }

    public static function getMainAddress($PersonId = null)
    {
        if (($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblAddress = $tblPerson->fetchMainAddress())
        ) {
            return $tblAddress->getGuiString();
        }

        return '';
    }
}