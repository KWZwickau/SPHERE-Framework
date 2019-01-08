<?php
namespace SPHERE\Application\Contact\Web;

use SPHERE\Application\Api\Contact\ApiWebToCompany;
use SPHERE\Application\Contact\Web\Service\Entity\TblToCompany;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Globe;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\TileBig;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\System\Extension\Extension;
use SPHERE\Common\Frontend\Link\Repository\Primary as PrimaryLink;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Contact\Web
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param $CompanyId
     * @param null $ToCompanyId
     * @param bool $setPost
     *
     * @return Form
     */
    public function formAddressToCompany($CompanyId, $ToCompanyId = null, $setPost = false)
    {

        if ($ToCompanyId && ($tblToCompany = Web::useService()->getWebToCompanyById($ToCompanyId))) {
            // beim Checken der Inputfeldern darf der Post nicht gesetzt werden
            if ($setPost) {
                $Global = $this->getGlobal();
                $Global->POST['Address'] = $tblToCompany->getTblWeb()->getAddress();
                $Global->POST['Type']['Type'] = $tblToCompany->getTblType()->getId();
                $Global->POST['Type']['Remark'] = $tblToCompany->getRemark();
                $Global->savePost();
            }
        }

        if ($ToCompanyId) {
            $saveButton = (new PrimaryLink('Speichern', ApiWebToCompany::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiWebToCompany::pipelineEditWebToCompanySave($CompanyId, $ToCompanyId));
        } else {
            $saveButton = (new PrimaryLink('Speichern', ApiWebToCompany::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiWebToCompany::pipelineCreateWebToCompanySave($CompanyId));
        }

        $tblTypeAll = Web::useService()->getTypeAll();

        return (new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Internet Adresse',
                            array(
                                (new SelectBox('Type[Type]', 'Typ',
                                    array('{{ Name }} {{ Description }}' => $tblTypeAll), new TileBig()
                                ))->setRequired(),
                                (new TextField('Address', 'Internet Adresse', 'Internet Adresse', new Globe() ))->setRequired()
                            ), Panel::PANEL_TYPE_INFO
                        ), 6),
                    new FormColumn(
                        new Panel('Sonstiges',
                            new TextArea('Type[Remark]', 'Bemerkungen', 'Bemerkungen', new Edit())
                            , Panel::PANEL_TYPE_INFO
                        ), 6),
                    new FormColumn(
                        $saveButton
                    )
                )),
            ))
        ))->disableSubmitAction();
    }

    // todo remove
    /**
     * @param TblCompany $tblCompany
     * @param null $Group
     *
     * @return Layout
     */
    public function frontendLayoutCompany(TblCompany $tblCompany, $Group = null)
    {

        $tblWebAll = Web::useService()->getWebAllByCompany($tblCompany);
        if ($tblWebAll !== false) {
            array_walk($tblWebAll, function (TblToCompany &$tblToCompany) use ($Group) {

                $Panel = array(
                    $tblToCompany->getTblWeb()->getAddress()
                );
                if ($tblToCompany->getRemark()) {
                    array_push($Panel, new Muted(new Small($tblToCompany->getRemark())));
                }

                $tblToCompany = new LayoutColumn(
                    new Panel(
                        new Globe() . ' ' . $tblToCompany->getTblType()->getName(), $Panel,
                        Panel::PANEL_TYPE_SUCCESS,

                        new Standard(
                            '', '/Corporation/Company/Web/Edit', new Edit(),
                            array('Id' => $tblToCompany->getId(), 'Group' => $Group),
                            'Bearbeiten'
                        )
                        . new Standard(
                            '', '/Corporation/Company/Web/Destroy', new Remove(),
                            array('Id' => $tblToCompany->getId(), 'Group' => $Group), 'Löschen'
                        )
                    )
                    , 3);
            });
        } else {
            $tblWebAll = array(
                new LayoutColumn(
                    new Warning('Keine Internet Adressen hinterlegt')
                )
            );
        }

        $LayoutRowList = array();
        $LayoutRowCount = 0;
        $LayoutRow = null;
        /**
         * @var LayoutColumn $tblWeb
         */
        foreach ($tblWebAll as $tblWeb) {
            if ($LayoutRowCount % 4 == 0) {
                $LayoutRow = new LayoutRow(array());
                $LayoutRowList[] = $LayoutRow;
            }
            $LayoutRow->addColumn($tblWeb);
            $LayoutRowCount++;
        }

        return new Layout(new LayoutGroup($LayoutRowList));
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return string
     */
    public function frontendLayoutCompanyNew(TblCompany $tblCompany)
    {

        if (($tblWebList = Web::useService()->getWebAllByCompany($tblCompany))){
            $LayoutRowList = array();
            $LayoutRowCount = 0;
            $LayoutRow = null;

            foreach ($tblWebList as $tblToCompany) {
                if (($tblWeb = $tblToCompany->getTblWeb())
                    && ($tblType = $tblToCompany->getTblType())
                ) {
                    $content = array();

                    $panelType = (preg_match('!Notfall!is',
                        $tblType->getName() . ' ' . $tblType->getDescription())
                        ? Panel::PANEL_TYPE_DANGER
                        : Panel::PANEL_TYPE_SUCCESS
                    );

                    $options =
                        (new Link(
                            new Edit(),
                            ApiWebToCompany::getEndpoint(),
                            null,
                            array(),
                            'Bearbeiten'
                        ))->ajaxPipelineOnClick(ApiWebToCompany::pipelineOpenEditWebToCompanyModal(
                            $tblCompany->getId(),
                            $tblToCompany->getId()
                        ))
                        . ' | '
                        . (new Link(
                            new \SPHERE\Common\Frontend\Text\Repository\Warning(new Remove()),
                            ApiWebToCompany::getEndpoint(),
                            null,
                            array(),
                            'Löschen'
                        ))->ajaxPipelineOnClick(ApiWebToCompany::pipelineOpenDeleteWebToCompanyModal(
                            $tblCompany->getId(),
                            $tblToCompany->getId()
                        ));

                    // funktioniert nur wenn http in der Internetadresse drin steht
//                    $content[] = new External(
//                        new Globe() . ' ' . $tblWeb->getAddress(),
//                        $tblWeb->getAddress(),
//                        null,
//                        array(),
//                        true,
//                        External::STYLE_LINK
//                    );
                    $content[] = $tblWeb->getAddress();

                    if (($remark = $tblToCompany->getRemark())) {
                        $content[] = new Muted($remark);
                    }

                    $panel = FrontendReadOnly::getContactPanel(
                        new Globe() . ' ' . $tblType->getName(),
                        $content,
                        $options,
                        $panelType
                    );

                    if ($LayoutRowCount % 4 == 0) {
                        $LayoutRow = new LayoutRow(array());
                        $LayoutRowList[] = $LayoutRow;
                    }
                    $LayoutRow->addColumn(new LayoutColumn($panel, 3));
                    $LayoutRowCount++;
                }
            }

            return (string) (new Layout(new LayoutGroup($LayoutRowList)));
        } else {
            return new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(new Warning('Keine Internet Adressen hinterlegt')))));
        }
    }
}
