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
use SPHERE\System\Extension\Repository\Debugger;
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

//            Debugger::devDump('Path '.$FileUpload->getPath());                      // /tmp
//            Debugger::devDump('Pathname '.$FileUpload->getPathname());              // /tmp/phpy26LIk
//            Debugger::devDump('RealPath '.$FileUpload->getRealPath());              // /tmp/phpy26LIk
//            Debugger::devDump('FileInfo '.$FileUpload->getFileInfo());              // /tmp/phpy26LIk
//            Debugger::devDump('ClientMimeType '.$FileUpload->getClientMimeType());  // image/jpeg
//            Debugger::devDump('MimeType '.$FileUpload->getMimeType());              // image/jpeg
//            Debugger::devDump('ClientSize '.$FileUpload->getClientSize());          // 376644
//            Debugger::devDump('Size '.$FileUpload->getSize());                      // 376644
//            Debugger::devDump('Type '.$FileUpload->getType());                      // file
//            exit;

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
            Debugger::devDump($_FILES['FileUpload']);

            $maxDim = 800;
            $smallDim = 70;
            $fileName = $_FILES['FileUpload']['tmp_name'];
            Debugger::devDump( getimagesize($fileName ));
            list($width, $height) = getimagesize( $fileName );
            $IsDim = false;
            if ( $width > $maxDim || $height > $maxDim ){
                $targetFilename = $fileName;
                $ratio = $width / $height;
                if($ratio > 1){
                    $newWidth = $maxDim;
                    $newHeight = $maxDim / $ratio;
                } else {
                    $newWidth = $maxDim * $ratio;
                    $newHeight = $maxDim;
                }
                $IsDim = true;
            }
//            if ( $width > $smallDim || $height > $smallDim ){
                $targetFilenameSmall = $fileName.'_Small';
                $ratio = $width / $height;
                if($ratio > 1){
                    $newSmallWidth = $smallDim;
                    $newSmallHeight = $smallDim / $ratio;
                } else {
                    $newSmallWidth = $smallDim * $ratio;
                    $newSmallHeight = $smallDim;
                }
//            }
                $src = imagecreatefromstring( file_get_contents( $fileName ) );

                if($IsDim){
                    $dst = imagecreatetruecolor( $newWidth, $newHeight );
                    imagecopyresampled( $dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height );
                    imagejpeg( $dst, $targetFilename ); // adjust format as ne
                    imagejpeg( $dst, $targetFilename ); // adjust format as needed
                    imagedestroy( $dst );
                }
                $dstSmall = imagecreatetruecolor( $newSmallWidth, $newSmallHeight );
                imagecopyresampled( $dstSmall, $src, 0, 0, 0, 0, $newSmallWidth, $newSmallHeight, $width, $height );
                imagejpeg( $dstSmall, sys_get_temp_dir().'/'.$targetFilenameSmall ); // adjust format as ne
//                imagedestroy( $dst );
                imagedestroy( $src );

                Debugger::devDump($dstSmall);

//                Debugger::devDump($dst);
//                Debugger::devDump( getimagesize($file_name ));
//                Debugger::devDump('<img src="data:image/jpeg;base64,'.base64_encode(stream_get_contents($dst)).'" style="border-radius: 15px;"/>');

            $Upload = $this->getUpload('FileUpload', sys_get_temp_dir(), true);
//            $UploadSmall = $this->getUpload($targetFilenameSmall, sys_get_temp_dir(), true);
//                ->validateMaxSize('2M')
//                ->validateMimeType(array(
//                    'image/png',
//                    'image/gif',
//                    'image/jpeg',
//                    'image/jpg',
//                ));
//                ->doUpload();
            Debugger::devDump($Upload);
//            Debugger::devDump($UploadSmall);

            $Dimension = $Upload->getDimensions();
//            Debugger::devDump($Upload->getName());
//            Debugger::devDump($Upload->getFilename());
//            Debugger::devDump($Upload->getExtension());
////            Debugger::devDump($Upload->getContent());
//            Debugger::devDump($Upload->getMimeType());
//            $Size = $Upload->getSize() / 1024000;
//            Debugger::devDump($Size);
//            Debugger::devDump($Dimension['width']);
//            Debugger::devDump($Dimension['height']);
            if(!($tblPerson = Person::useService()->getPersonById($PersonId))){
                $form .= new Danger('Person nicht gefunden');
            }


            if (!(new Data($this->getBinding()))->createPicture(
                $tblPerson, stream_get_contents($dstSmall)// $Upload->getContent()

//                $Upload->getName(),
//                $Upload->getFilename(),
//                $Upload->getExtension(),
//                $Upload->getContent(),
//                $Upload->getMimeType(),
//                $Upload->getSize(),
//                $Dimension['width'],
//                $Dimension['height']
            )) {
                $form .= new Danger('Der Upload konnte nicht erfasst werden');
            } else {
                $form .= new Success('Der Upload ist erfasst');
//                $form .= new Success('Der Upload ist erfasst')
//                .new Redirect('/Platform/System/Test/TestSite', Redirect::TIMEOUT_SUCCESS);
                return $form;
            }
            unlink($Upload->getLocation().DIRECTORY_SEPARATOR.$Upload->getFilename());

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
