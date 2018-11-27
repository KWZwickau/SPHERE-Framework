<?php
namespace SPHERE\Application\Billing\Accounting\Debtor;

use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Button\Standard as StandardForm;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Listing as ListingIcon;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Billing\Accounting\Debtor
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null $GroupId
     *
     * @return Stage
     */
    public function frontendDebtor($GroupId = null)
    {

        $Stage = new Stage('Beitragszahler', '');

        $Content = array();


        if(($tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_CUSTODY))){
            $Content[] = new Center('Auswahl für '.$tblGroup->getName()
                .new Container(new Standard('', __NAMESPACE__.'/View', new ListingIcon(), array('GroupId' => $tblGroup->getId()))));
        }

        if(($tblGroupList = Group::useService()->getGroupAll())){
            foreach($tblGroupList as &$tblGroup){
                if($tblGroup->getMetaTable() === TblGroup::META_TABLE_CUSTODY
                ){
                    $tblGroup = false;
                }
            }
            $tblGroupList = array_filter($tblGroupList);
        }
        if(false === $tblGroupList
            || empty($tblGroupList)){
            $tblGroupList = array();
        }

        $Content[] = new Center('Auswahl für Personen'
            .new Container(
                new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn('', 3),
                    new LayoutColumn(Debtor::useService()->directRoute(
                        new Form(new FormGroup(new FormRow(array(new FormColumn(new SelectBox('GroupId', '', array('{{ Name }}' => $tblGroupList)), 11)
                        , new FormColumn(new StandardForm('', new ListingIcon()), 1))))), $GroupId)
                        , 6)
                ))))
            ));

        $Stage->setContent(new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        ''
                        , 3),
                    new LayoutColumn(
                        new Panel('Kategorien:', new Listing($Content))
                        , 6)
                ))
            )
        ));


        return $Stage;
    }

    public function frontendDebtorView($GroupId = null)
    {

        $GroupName = '';
        if(($tblGroup = Group::useService()->getGroupById($GroupId))){
            $GroupName = $tblGroup->getName();
        }
        $Stage = new Stage('Beitragsverursacher der Gruppe: '.$GroupName);
        $Stage->addButton(new Standard('Zurück', __NAMESPACE__, new ChevronLeft()));

        $Stage->setContent(new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        $this->getCauserTable($GroupId)
                    )
                ))
            )
        ));

        return $Stage;
    }

    public function getCauserTable($GroupId)
    {

        $TableContent = array();
        if(($tblGroup = Group::useService()->getGroupById($GroupId))){
            if(($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))){
                $i = 0;
                array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, $tblGroup, &$i){
                    $Item['Name'] = $tblPerson->getLastFirstName();
                    $Item['DebtorNumber'] = '';
                    array_push($TableContent, $Item);
                });
            }
        }

        return new TableData($TableContent, null, array(
            'Name' => 'Person',
            'DebtorNumber' => 'Debitor Nr.',
//            'Option' => '',
        ));
    }
}