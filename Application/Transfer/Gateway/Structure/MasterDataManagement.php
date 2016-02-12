<?php
namespace SPHERE\Application\Transfer\Gateway\Structure;

use SPHERE\Application\Transfer\Gateway\Converter\Output;
use SPHERE\Application\Transfer\Gateway\Fragment\Contact;
use SPHERE\Application\Transfer\Gateway\Fragment\Corporation;
use SPHERE\Application\Transfer\Gateway\Fragment\People;
use SPHERE\Application\Transfer\Gateway\Item\Contact\Address;
use SPHERE\Application\Transfer\Gateway\Item\Company;
use SPHERE\Application\Transfer\Gateway\Item\Contact\Mail;
use SPHERE\Application\Transfer\Gateway\Item\Contact\Phone;
use SPHERE\Application\Transfer\Gateway\Item\Person;
use SPHERE\Common\Roadmap\Roadmap;

/**
 * Class MasterDataManagement
 *
 * @package SPHERE\Application\Transfer\Gateway\Structure
 */
class MasterDataManagement extends AbstractStructure
{

    /**
     * MasterDataManagement constructor.
     */
    public function __construct()
    {
        $Roadmap = new Roadmap();
        $this->Output = new Output( $Roadmap->getVersionNumber() );

        $this->People = new People();
        $this->Output->addFragment($this->People);

        $this->PeopleGroup = new People\Group();
        $this->People->addFragment($this->PeopleGroup);

        $this->Meta = new People\Meta();
        $this->People->addFragment($this->Meta);

        $this->PeopleContact = new Contact();
        $this->People->addFragment($this->PeopleContact);

        $this->Corporation = new Corporation();
        $this->Output->addFragment($this->Corporation);

        $this->CorporationContact = new Contact();
        $this->Corporation->addFragment($this->CorporationContact);
    }

    /**
     * @param Person $Person
     *
     * @return $this
     */
    public function addPerson(Person $Person)
    {

        $this->People->addItem( $Person );
        return $this;
    }

    /**
     * @param Company $Company
     *
     * @return $this
     */
    public function addCompany(Company $Company)
    {

        $this->Corporation->addItem( $Company );
        return $this;
    }

    /**
     * @param Person  $Person
     * @param Address $Address
     *
     * @return $this
     */
    public function addPersonAddress(Person $Person, Address $Address)
    {
        $Address->setXmlReference( $Person->getXmlIdentifier() );
        $this->PeopleContact->addItem( $Address );
        return $this;
    }

    /**
     * @param Person  $Person
     * @param Person\Group $Group
     *
     * @return $this
     */
    public function addPersonGroup(Person $Person, Person\Group $Group)
    {
        $Group->setXmlReference( $Person->getXmlIdentifier() );
        $this->PeopleGroup->addItem( $Group );
        return $this;
    }

    /**
     * @param Person  $Person
     * @param Phone $Phone
     *
     * @return $this
     */
    public function addPersonPhone(Person $Person, Phone $Phone)
    {
        $Phone->setXmlReference( $Person->getXmlIdentifier() );
        $this->PeopleContact->addItem( $Phone );
        return $this;
    }

    /**
     * @param Person  $Person
     * @param Mail $Mail
     *
     * @return $this
     */
    public function addPersonMail(Person $Person, Mail $Mail)
    {
        $Mail->setXmlReference( $Person->getXmlIdentifier() );
        $this->PeopleContact->addItem( $Mail );
        return $this;
    }

    /**
     * @param Company $Company
     * @param Address $Address
     *
     * @return $this
     */
    public function addCompanyAddress(Company $Company, Address $Address)
    {

        $Address->setXmlReference( $Company->getXmlIdentifier() );
        $this->CorporationContact->addItem( $Address );
        return $this;
    }

    /**
     * @param Company  $Company
     * @param Phone $Phone
     *
     * @return $this
     */
    public function addCompanyPhone(Company $Company, Phone $Phone)
    {
        $Phone->setXmlReference( $Company->getXmlIdentifier() );
        $this->CorporationContact->addItem( $Phone );
        return $this;
    }

    /**
     * @return string
     */
    public function getXml()
    {

        return $this->Output->getXml();
    }
}
