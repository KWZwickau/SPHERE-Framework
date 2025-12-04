<?php
namespace SPHERE\Application\Education\School\Type;

use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\School\Type
 */
class Frontend implements IFrontendInterface
{
    public function frontendSchoolType(): Stage
    {
        $stage = new Stage('Schulart', 'Übersicht');
        $dataList = [];
        if (($tblTypeList = Type::useService()->getTypeAllAll())) {
            foreach ($tblTypeList as $tblType) {
                $dataList[] = [
                    'Acronym' => $tblType->getShortName(),
                    'Name' => $tblType->getName(),
                    'Category' => $tblType->getTblCategory() ? $tblType->getTblCategory()->getName() : '',
                    'Basic' => $tblType->getIsBasic() ? 'Ja' : 'Nein'
                ];
            }
        }

        $stage->setContent(
            new TableData(
                $dataList,
                null,
                array(
                    'Acronym' => 'Kürzel',
                    'Name' => 'Name',
                    'Category' => 'Kategorie',
                    'Basic' => 'Standard'
                ),
                array(
                    'order' => array(
                        array(3, 'asc'),
                        array(1, 'asc'),
                    ),
                    'paging'     => false,
                )
            )
        );

        return $stage;
    }
}
