<?php

namespace SPHERE\Application\RestApi;

use SPHERE\Application\RestApi\Education\Absence\ApiAbsence;
use SPHERE\Application\RestApi\Education\ClassRegister\ApiTimeTable;
use SPHERE\Application\RestApi\Education\Grade\ApiGrade;
use SPHERE\Application\RestApi\Menu\ApiMenu;
use SPHERE\Application\RestApi\Person\ApiPerson;
use SPHERE\Application\RestApi\Public\Authorization\ApiAuthorization;
use SPHERE\Application\RestApi\Public\Indiware\ApiIndiware;

class RestApi implements IApiInterface
{
    /**
     * @return void
     */
    public static function registerApi(): void
    {
        ApiIndiware::registerApi();
//        ApiAuthorization::registerApi();
//        ApiPerson::registerApi();
        ApiMenu::registerApi();
        ApiAbsence::registerApi();
        ApiGrade::registerApi();
        ApiTimeTable::registerApi();
    }
}