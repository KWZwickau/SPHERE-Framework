<?php

namespace SPHERE\Application\Api\Document\Standard\Repository;

use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReport\B01;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReport\B01_1;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReport\B02;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReport\C01;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReport\E01;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReport\E02;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReport\E02_1;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReport\E03;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReport\E04;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReport\E04_1;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReport\E05;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReport\E07;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReport\E08;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReport\E11;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReport\F01;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReport\G01;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;

/**
 * Class KamenzReport
 * @package SPHERE\Application\Api\Document\Standard\Repository
 */
class KamenzReport extends AbstractDocument
{
    /**
     * @return string
     */
    public function getName()
    {

        return 'Kamenz-Statistik';
    }

    /**
     *
     * @param array $pageList
     * @param string $Part
     *
     * @return Frame
     */
    public function buildDocument(array $pageList = array(), string $Part = '0'): Frame
    {
        return (new Frame())->addDocument((new Document())
            ->addPage((new Page())
                ->addSliceArray(B01::getContent())
                ->addSliceArray(B01_1::getContent())
                ->addSliceArray(B02::getContent())
                ->addSliceArray(C01::getContent())
            )
            ->addPage((new Page())
                ->addSliceArray(E01::getContent())
                ->addSliceArray(E02::getContent())

            )
            ->addPage((new Page())
                ->addSliceArray(E02_1::getContent())
                ->addSliceArray(E03::getContent())
            )
            ->addPage((new Page())
                ->addSliceArray(E04::getContent())
                ->addSliceArray(E04_1::getContent())
                ->addSliceArray(E05::getContent())
            )
            ->addPage((new Page())
                ->addSliceArray(E07::getContent())
                ->addSliceArray(E08::getContent())
            )
            ->addPage((new Page())
                ->addSliceArray(E11::getContent())
            )
            ->addPage((new Page())
                ->addSliceArray(F01::getContent(0,4))
            )
            ->addPage((new Page())
                ->addSliceArray(F01::getContent(5,7))
                ->addSliceArray(G01::getContent())
            )
        );
    }
}