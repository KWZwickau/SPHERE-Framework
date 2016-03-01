<?php
namespace SPHERE\Application\Transfer\Gateway\Operation;

use SPHERE\Application\Contact\Address\Service\Entity\TblAddress;
use SPHERE\Application\Contact\Address\Service\Entity\TblCity;
use SPHERE\Application\Contact\Mail\Service\Entity\TblMail;
use SPHERE\Application\Contact\Phone\Service\Entity\TblPhone;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Transfer\Gateway\Converter\AbstractConverter;
use SPHERE\Application\Transfer\Gateway\Converter\FieldPointer;
use SPHERE\Application\Transfer\Gateway\Converter\FieldSanitizer;
use SPHERE\Application\Transfer\Gateway\Item\Contact\Address;
use SPHERE\Application\Transfer\Gateway\Item\Contact\Mail;
use SPHERE\Application\Transfer\Gateway\Item\Contact\Phone;
use SPHERE\Application\Transfer\Gateway\Item\Person;
use SPHERE\Application\Transfer\Gateway\Structure\AbstractStructure;

class FESH extends AbstractConverter
{
    public function __construct($File, AbstractStructure $Structure)
    {

        $this->loadFile($File);
        $this->setStructure( $Structure );

        // Default

        $this->addSanitizer(array($this, 'sanitizeFullTrim'));

        // Group

        $this->addSanitizer(array($this, 'sanitizeAddressCityCode'), Address::FIELD_CITY_CODE);
        $this->addSanitizer(array($this, 'sanitizeAddressCityName'), Address::FIELD_CITY_NAME);
        $this->addSanitizer(array($this, 'sanitizeAddressCityDistrict'), Address::FIELD_CITY_DISTRICT);

        // Specific

        $this->setPointer(new FieldPointer('B', Person::FIELD_FIRST_NAME));
        $this->setPointer(new FieldPointer('C', Person::FIELD_LAST_NAME));

        $this->setPointer(new FieldPointer('E', Address::FIELD_CITY_CODE));
        $this->setPointer(new FieldPointer('F', Address::FIELD_CITY_NAME));
        $this->setPointer(new FieldPointer('F', Address::FIELD_CITY_DISTRICT));

        $this->setPointer(new FieldPointer('G', Address::FIELD_STREET_NAME));
        $this->setPointer(new FieldPointer('H', Address::FIELD_STREET_NUMBER));
        $this->setPointer(new FieldPointer('I', Phone::FIELD_NUMBER));
        $this->setPointer(new FieldPointer('J', Mail::FIELD_ADDRESS))
            ->setSanitizer(new FieldSanitizer('J', Mail::FIELD_ADDRESS, array($this, 'sanitizeMailAddress')));

        $this->setPointer(new FieldPointer('K', Person::FIELD_FIRST_NAME))
            ->setSanitizer(new FieldSanitizer('K', Person::FIELD_FIRST_NAME, array($this, 'sanitizeCustodyFirstName')));
        $this->setPointer(new FieldPointer('K', Person::FIELD_LAST_NAME))
            ->setSanitizer(new FieldSanitizer('K', Person::FIELD_LAST_NAME, array($this, 'sanitizeCustodyLastName')));

        $this->setPointer(new FieldPointer('L', Person::FIELD_FIRST_NAME))
            ->setSanitizer(new FieldSanitizer('L', Person::FIELD_FIRST_NAME, array($this, 'sanitizeCustodyFirstName')));
        $this->setPointer(new FieldPointer('L', Person::FIELD_LAST_NAME))
            ->setSanitizer(new FieldSanitizer('L', Person::FIELD_LAST_NAME, array($this, 'sanitizeCustodyLastName')));

        $this->scanFile(1);
    }

    public function runConvert($Row)
    {

        // Fertige Zeile,

//        print_r($Row);
        $Person = new Person(new TblPerson());
        $Person->setPayload(array_merge($Row['B'], $Row['C']));
        $this->getStructure()->addPerson( $Person );

        $Group = new Person\Group(new TblGroup(''));
        $Group->setPayload( array( 'Name' => 'Interessent', 'MetaTable' => 'Interessent') );
        $this->getStructure()->addPersonGroup( $Person, $Group );

        $Address = new Address(array(new TblAddress(),new TblCity()));
        $Address->setPayload(array_merge($Row['E'], $Row['F'], $Row['G'], $Row['H']));
        $this->getStructure()->addPersonAddress( $Person, $Address);

        if( !empty($Row['I'][Phone::FIELD_NUMBER]) ) {
            $Phone = new Phone(array(new TblPhone()));
            $Phone->setPayload(array_merge($Row['I']));
            $this->getStructure()->addPersonPhone($Person, $Phone);
        }

        if( !empty($Row['J'][Mail::FIELD_ADDRESS]) ) {
            $Mail = new Mail(array(new TblMail()));
            $Mail->setPayload(array_merge($Row['J']));
            $this->getStructure()->addPersonMail($Person, $Mail);
        }

        $Person = new Person(new TblPerson());
        $Person->setPayload(array_merge($Row['K']));
        $this->getStructure()->addPerson( $Person );

        $Address = new Address(array(new TblAddress(),new TblCity()));
        $Address->setPayload(array_merge($Row['E'], $Row['F'], $Row['G'], $Row['H']));
        $this->getStructure()->addPersonAddress( $Person, $Address);

        if( !empty($Row['I'][Phone::FIELD_NUMBER]) ) {
            $Phone = new Phone(array(new TblPhone()));
            $Phone->setPayload(array_merge($Row['I']));
            $this->getStructure()->addPersonPhone($Person, $Phone);
        }

        if( !empty($Row['J'][Mail::FIELD_ADDRESS]) ) {
            $Mail = new Mail(array(new TblMail()));
            $Mail->setPayload(array_merge($Row['J']));
            $this->getStructure()->addPersonMail($Person, $Mail);
        }

        $Person = new Person(new TblPerson());
        $Person->setPayload(array_merge($Row['L']));
        $this->getStructure()->addPerson( $Person );

        $Address = new Address(array(new TblAddress(),new TblCity()));
        $Address->setPayload(array_merge($Row['E'], $Row['F'], $Row['G'], $Row['H']));
        $this->getStructure()->addPersonAddress( $Person, $Address);

        if( !empty($Row['I'][Phone::FIELD_NUMBER]) ) {
            $Phone = new Phone(array(new TblPhone()));
            $Phone->setPayload(array_merge($Row['I']));
            $this->getStructure()->addPersonPhone($Person, $Phone);
        }

        if( !empty($Row['J'][Mail::FIELD_ADDRESS]) ) {
            $Mail = new Mail(array(new TblMail()));
            $Mail->setPayload(array_merge($Row['J']));
            $this->getStructure()->addPersonMail($Person, $Mail);
        }
    }
}
