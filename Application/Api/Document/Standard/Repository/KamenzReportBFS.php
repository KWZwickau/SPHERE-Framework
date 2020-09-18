<?php

namespace SPHERE\Application\Api\Document\Standard\Repository;

use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS\B01;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS\B02;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS\F01;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;

/**
 * Class KamenzReportBFS
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository
 */
class KamenzReportBFS extends AbstractDocument
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
     *
     * @return Frame
     */
    public function buildDocument($pageList = array())
    {
        return (new Frame())->addDocument((new Document())
//            ->addPage((new Page())
//                ->addSliceArray(B01::getContent('B01'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(B01::getContent('B01_1'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(B02::getContent('B02_1'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(B02::getContent('B02_1_1'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(B02::getContent('B02_2'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(B02::getContent('B02_2_1'))
//            )
            ->addPage((new Page())
                ->addSliceArray(F01::getContent('F01_1'))
            )
            ->addPage((new Page())
                ->addSliceArray(F01::getContent('F01_2'))
            )
        );
    }
}