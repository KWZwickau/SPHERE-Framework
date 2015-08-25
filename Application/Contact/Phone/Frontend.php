<?php
namespace SPHERE\Application\Contact\Phone;

use SPHERE\Application\Contact\Phone\Service\Entity\TblToPerson;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Icon\Repository\MapMarker;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Contact\Phone
 */
class Frontend implements IFrontendInterface
{

    /**
     * @param TblPerson $tblPerson
     *
     * @return Layout
     */
    public function frontendLayoutPerson( TblPerson $tblPerson )
    {

        $tblMailAll = Phone::useService()->getPhoneAllByPerson( $tblPerson );
        if ($tblMailAll !== false) {
            array_walk( $tblMailAll, function ( TblToPerson &$tblToPerson ) {

                $tblToPerson = new LayoutColumn(
                    new Panel(
                        new MapMarker().' '.$tblToPerson->getTblType()->getName(), array(
                        $tblToPerson->getTblPhone()->getNumber()
                    ), Panel::PANEL_TYPE_INFO,
                        new Danger(
                            '', '/Destroy', new Remove(),
                            array( 'Id' => $tblToPerson->getId() ), 'Löschen'
                        )
                    )
                    , 4 );
            } );
        } else {
            $tblMailAll = array(
                new LayoutColumn(
                    new Warning( 'Keine Telefonnummern hinterlegt' )
                )
            );
        }
        return new Layout( new LayoutGroup( new LayoutRow( $tblMailAll ) ) );
    }
}
