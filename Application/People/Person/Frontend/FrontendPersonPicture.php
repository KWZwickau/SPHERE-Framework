<?php
namespace SPHERE\Application\People\Person\Frontend;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Api\Document\Storage\ApiPersonPicture;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\FileUpload;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\WellReadOnly;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class FrontendPersonPicture
 *
 * @package SPHERE\Application\People\Person\Frontend
 */
class FrontendPersonPicture extends FrontendReadOnly
{
    const TITLE = 'Personendaten';

    /**
     * @param                   $PersonId
     * @param                   $Group
     * @param UploadedFile|null $FileUpload
     *
     * @return string
     * @throws \MOC\V\Core\FileSystem\Exception\FileSystemException
     */
    public static function getPersonPictureContent($PersonId = null, $Group = null)
    {
        $Image = '';

        $PictureHeight = '362px';
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
            $Image = '<img src="'.$File->getLocation().'" style="border-radius: '.$PictureBorderRadius.'; height: '.$PictureHeight.'; margin-top: '.$PictureMarginTop.'">';
        }

        return new Center((new Link($Image, '#'))->ajaxPipelineOnClick(ApiPersonPicture::pipelineEditPersonPicture($PersonId, $Group)));
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public static function getEditPersonPictureContent($PersonId = null, $Group = null, UploadedFile $FileUpload = null)
    {
        $Image = '';

        $PictureHeight = '244px';
        $PictureBorderRadius = '0';
        $PictureMarginTop = '43px';
        if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $tblPersonPicture = Storage::useService()->getPersonPictureByPerson($tblPerson);
            if($tblPersonPicture){
                $Image = $tblPersonPicture->getPicture($PictureHeight, $PictureBorderRadius, $PictureMarginTop);
            }
        }

        if(!$Image){
            $File = FileSystem::getFileLoader('/Common/Style/Resource/SSWAbsence - Kopie.png');
            $Image = '<img src="'.$File->getLocation().'" style="border-radius: '.$PictureBorderRadius.'; height: '.$PictureHeight.'; margin-top: '.$PictureMarginTop.'">';
        }
        $Image.= '<div style="height: 7px">&nbsp;</div>';

//        $ButtonAbort = new Standard('Abbrechen', '/People/Person', new Disable(), array('Id' => $PersonId, 'Group' => $Group)); //->ajaxPipelineOnClick(ApiPersonPicture::pipelineLoadPersonPictureContent($PersonId, $Group));
        $ButtonAbort = (new Standard('Abbrechen', '/People/Person', new Disable()))->ajaxPipelineOnClick(ApiPersonPicture::pipelineLoadPersonPictureContent($PersonId, $Group));
        $ButtonDelete = new Danger('');
        if(isset($tblPersonPicture) && $tblPersonPicture){
            $ButtonDelete = (new DangerLink('Löschen', '#', new Disable()))->ajaxPipelineOnClick(ApiPersonPicture::pipelineRemovePersonPicture($PersonId, $Group));
        }
        $IsUpload = 0;
        if(!$FileUpload){
            $_POST['IsUpload'] = $IsUpload = 1;
        }

        $ToolTipContent = new Container('Format: '.new Bold('JPG/JPEG, PNG'))
        .new Container('empfohlenes Seitenverhältnis: '.new Bold('Hochformat (3,5 x 4,5)'))
//        .new Container(new Bold('Hochformat (3,5 x 4,5)'))
        .new Container('Maximale Speichergröße: '.new Bold('6 MB'));

        $form = new Form(new FormGroup(new FormRow(array(
            new FormColumn(new Container(new Center($Image))),
            new FormColumn((new FileUpload('FileUpload', '', 'Foto hochladen '.(new ToolTip(new Info(), htmlspecialchars($ToolTipContent)))
                    ->enableHtml()))->setRequired()),
            new FormColumn(new HiddenField('IsUpload')),
            new FormColumn(array(new Primary('Speichern', new Save()), $ButtonAbort, $ButtonDelete))
        ),
        )),null, false);

//        $form->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Content = new WellReadOnly(
            Storage::useService()->createPersonPicture($form, $PersonId, $Group, $FileUpload, $IsUpload)
        );

        return $Content;
    }
}