<?php
namespace SPHERE\Application\Platform\System\Test\Service;

use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Platform\System\Test\Service\Entity\TblTestPicture;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\DataCacheable;

/**
 * Class Data
 *
 * @package SPHERE\Application\Platform\System\Test\Service
 */
class Data extends DataCacheable
{

    /** @var null|Binding $Connection */
    private $Connection = null;

    /**
     * @param Binding $Connection
     */
    function __construct(Binding $Connection)
    {

        $this->Connection = $Connection;
    }

    public function setupDatabaseContent()
    {

//        $this->createType( 'Sorgeberechtigt' );
    }

    /**
     * @param        $Name
     * @param        $FileName
     * @param        $Extension
     * @param $File
     * @param $Type
     * @param        $Size
     * @param        $Width
     * @param        $Height
     *
     * @return null|TblTestPicture
     */
    public function createTestPicture($Name, $FileName, $Extension, $File, $Type, $Size, $Width, $Height)
    {

        $Manager = $this->Connection->getEntityManager();
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
            Protocol::useService()->createInsertEntry($this->Connection->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblTestPicture
     */
    public function getTestPictureById($Id)
    {

        $Entity = $this->Connection->getEntityManager()->getEntityById('TblTestPicture', $Id);

        return ( null === $Entity ? false : $Entity );
    }


    /**
     * @return bool|TblTestPicture[]
     */
    public function getTestPictureAll()
    {

        $EntityList = $this->Connection->getEntityManager()->getEntity('TblTestPicture')->findAll();

        return ( empty( $EntityList ) ? false : $EntityList );
    }
}
