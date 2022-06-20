<?php
namespace SPHERE\Application\Platform\System\Test;

use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Test\Service\Data;
use SPHERE\Application\Platform\System\Test\Service\Entity\TblPicture;
use SPHERE\Application\Platform\System\Test\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Service
 *
 * @package SPHERE\Application\Platform\System\Test
 */
class Service extends AbstractService
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8)
    {

        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param IFormInterface|null $form
     * @param int|null            $PersonId
     * @param UploadedFile|null   $FileUpload
     *
     * @return IFormInterface|string|null
     */
    public function uploadNow(IFormInterface &$form = null, $PersonId = null, UploadedFile $FileUpload = null)
    {

        /**
         * Skip to Frontend
         */
        if (null === $FileUpload
        ) {
            return $form;
        }

        if (!$FileUpload) {
            $form->setError('FileUpload', 'Bitte wählen Sie eine Datei');
            return $form;
        }

        try {
            if($_FILES['FileUpload']['error']){
                $form->setError('FileUpload', 'Datei überschreitet die Grenzwerte.');
                return $form;
            }
            switch ($_FILES['FileUpload']['type']) {
                case 'image/jpg':
                case 'image/jpeg':
                case 'image/png':
                case 'image/git':
                    break;
                default:
                    $form->setError('FileUpload', 'Datei mit dem MimeType ('.$_FILES['FileUpload']['type'].') ist nicht erlaubt.');
                    return $form;
            }

            $maxDim = 500;
            $fileName = $_FILES['FileUpload']['tmp_name'];
            list($width, $height) = getimagesize( $fileName );
            if ( $width > $maxDim || $height > $maxDim ){
                $ratio = $width / $height;
                if($ratio > 1){
                    $newWidth = $maxDim;
                    $newHeight = $maxDim / $ratio;
                } else {
                    $newWidth = $maxDim * $ratio;
                    $newHeight = $maxDim;
                }
            } else {
                $newWidth = $width;
                $newHeight = $height;
            }

            // skalieren
            $src = imagecreatefromstring(file_get_contents($fileName));
            $dst = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagejpeg($dst, $fileName); // adjust format as needed
            imagedestroy($src);
            imagedestroy($dst);
//            $Dimension = $Upload->getDimensions();
            if(!($tblPerson = Person::useService()->getPersonById($PersonId))){
                $form .= new Danger('Person nicht gefunden');
            }


            if (!(new Data($this->getBinding()))->createPicture(
                $tblPerson, file_get_contents($fileName )// $Upload->getContent()
            )) {
                $form .= new Danger('Der Upload konnte nicht erfasst werden');
            } else {
                $form .= new Success('Der Upload ist erfasst');
//                .new Redirect('/Platform/System/Test/TestSite', Redirect::TIMEOUT_SUCCESS);
            }
            unlink($fileName);
            return $form;

        } catch (\Exception $Exception) {
            if(json_decode($Exception->getMessage())){
                $ArrayExeption = json_decode($Exception->getMessage());
            } else {
                $ArrayExeption = array($Exception->getMessage());
            }
            if($ArrayExeption){
                foreach($ArrayExeption as &$ExeptionMessage){
                    switch ($ExeptionMessage){
                        case 'The uploaded file exceeds the upload_max_filesize directive in php.ini':
                            $ExeptionMessage = 'Der Anhang überschreitet die maximale Größe von '.ini_get('upload_max_filesize').'B';
                            break;
                        case 'The uploaded file was not sent with a POST request':
                            $ExeptionMessage = 'Das Ticket konnte nicht erstellt werden';
                    }
                }
                $form->setError('FileUpload', new Listing($ArrayExeption));
            }
            // Fehler beim return verarbeitbar
            $Error = true;
        }

        return $form;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblPicture
     */
    public function getPictureByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getPictureByPerson($tblPerson);
    }

    /**
     * @param TblPicture $tblPicture
     *
     * @return string
     */
    public function destroyPicture(TblPicture $tblPicture)
    {

        (new Data($this->getBinding()))->destroyPicture($tblPicture);
        return new Success('Das Bild wurde erfolgreich gelöscht')
            .new Redirect('/Platform/System/Test/TestSite', 1);
    }
}
