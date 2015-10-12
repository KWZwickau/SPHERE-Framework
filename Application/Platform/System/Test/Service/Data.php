<?php
namespace SPHERE\Application\Platform\System\Test\Service;

use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Platform\System\Test\Service\Entity\TblTestPicture;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Platform\System\Test\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

//        $this->createType( 'Sorgeberechtigt' );
    }

    /**
     * @param        $Name
     * @param        $FileName
     * @param        $Extension
     * @param        $File
     * @param        $Type
     * @param        $Size
     * @param        $Width
     * @param        $Height
     *
     * @return null|TblTestPicture
     */
    public function createTestPicture($Name, $FileName, $Extension, $File, $Type, $Size, $Width, $Height)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = null;
//        $Entity = $Manager->getEntity( 'TblTestPicture' )->findOneBy( array(
//            TblTestPicture::ATTR_IMG_DATA => $File
//        ) );

        if (null === $Entity) {
            $Entity = new TblTestPicture();
            $Entity->setName($Name);
            $Entity->setFileName($FileName);
            $Entity->setExtension($Extension);
            $Entity->setImgData($File);
            $Entity->setImgType($Type);
            $Entity->setSize($Size);
            $Entity->setWidth($Width);
            $Entity->setHeight($Height);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblTestPicture $tblTestPicture
     *
     * @return bool
     */
    public function removeTestPicture(TblTestPicture $tblTestPicture)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntityById('TblTestPicture', $tblTestPicture->getId());

        if (null !== $Entity) {

            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);

            return true;
        }

        return false;
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblTestPicture
     */
    public function getTestPictureById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblTestPicture', $Id);

        return ( null === $Entity ? false : $Entity );
    }


    /**
     * @return bool|TblTestPicture[]
     */
    public function getTestPictureAll()
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblTestPicture')->findAll();

        return ( empty( $EntityList ) ? false : $EntityList );
    }
}
