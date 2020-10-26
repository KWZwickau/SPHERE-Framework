<?php

namespace SPHERE\Application\Api\Document\Standard\Repository;

use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS\F01;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportFS\B01;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportFS\B02;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportFS\K01;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportFS\N01;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportFS\N03;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportFS\N03_2;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportFS\N05;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;

/**
 * Class KamenzReportFS
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository
 */
class KamenzReportFS extends AbstractDocument
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
//            ->addPage((new Page())
//                ->addSliceArray(F01::getContent('F01_1', 'Fachschule'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(F01::getContent('F01_2', 'Fachschule'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(K01::getContent())
//            )
//            ->addPage((new Page())
//                ->addSliceArray(N01::getContent('N01'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(N01::getContent('N01_1'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(N01::getContent('N02'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(N01::getContent('N02_1'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(N03::getContent('N03_1'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(N03::getContent('N03_1_1'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(N03_2::getContent('N03_2'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(N03_2::getContent('N03_2_1'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(N03::getContent('N04_1'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(N03_2::getContent('N04_2'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(N05::getContent('N05'))
//            )
            ->addPage((new Page())
                ->addSliceArray(N05::getContent('N05_1'))
            )
        );
    }
}