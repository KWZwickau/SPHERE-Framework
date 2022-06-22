<?php
namespace SPHERE\Application\People\Person\Frontend;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Link\Repository\Link;

/**
 * Class FrontendPersonPicture
 *
 * @package SPHERE\Application\People\Person\Frontend
 */
class FrontendPersonPicture extends FrontendReadOnly
{
    const TITLE = 'Personendaten';

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public static function getPersonPictureContent($PersonId = null)
    {
        $Image = '';

        $PictureHeight = '360px';
        $PictureBorderRadius = '10px';
        $PictureMarginTop = '49px';

        if (($tblPerson = Person::useService()->getPersonById($PersonId))) {

            $tblPersonPicture = Storage::useService()->getPersonPictureByPerson($tblPerson);
            if($tblPersonPicture){
                $Image = $tblPersonPicture->getPicture($PictureHeight, $PictureBorderRadius, $PictureMarginTop);
            }
        }

        if(!$Image){
            $File = FileSystem::getFileLoader('/Common/Style/Resource/SSWAbsence - Kopie.png');
            $Image = new Link('<img src="'.$File->getLocation().'" style="border-radius: '.$PictureBorderRadius.'; height: '.$PictureHeight.'; padding-top: '.$PictureMarginTop.'">', '#');
        }

        return $Image;
    }
}