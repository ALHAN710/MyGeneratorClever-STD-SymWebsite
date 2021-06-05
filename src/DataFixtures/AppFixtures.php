<?php

namespace App\DataFixtures;

use Faker;
use App\Entity\Site;
use App\Entity\User;
use App\Entity\SmartMod;
use App\Entity\Enterprise;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    //Constructeur pour utiliser la fonction d'encodage de mot passe
    //encodePassword($entity, $password)
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Faker\Factory::create('fr_FR');
        $faker->seed(1337);

        $enterprise = new Enterprise();
        $enterprise->setSocialReason('ST DIGITAL CAMEROUN')
            ->setAddress('75 rue Alliance Française – Imm Entrelec Bali BP 32 Douala')
            ->setPhoneNumber('(+ 237) 243702420 / (+ 237) 696963163')
            ->setCountry('Cameroon')
            ->setEmail('info@st.digital');
        $manager->persist($enterprise);

        $superAdminUser = new User();
        $superAdminUser->setEmail('alhadoumpascal@gmail.com')
            ->setFirstName('Pascal')
            ->setLastName('ALHADOUM')
            ->setPassword($this->encoder->encodePassword($superAdminUser, 'password'))
            ->setRoles(['ROLE_SUPER_ADMIN'])
            //->setVerified(true)
            ->setPhoneNumber('690442311')
            ->setCountryCode('+237');
        $manager->persist($superAdminUser);

        $superAdminUser1 = new User();
        $superAdminUser1->setEmail('cabrelmbakam@gmail.com')
            ->setFirstName('Cabrel')
            ->setLastName('MBAKAM')
            ->setPassword($this->encoder->encodePassword($superAdminUser1, 'password'))
            ->setRoles(['ROLE_SUPER_ADMIN'])
            //->setVerified(true)
            ->setPhoneNumber('690304593')
            ->setCountryCode('+237');
        $manager->persist($superAdminUser1);

        $adminUser = new User();
        $adminUser->setEmail('jean-francis@st.digital')
            ->setFirstName('Jean-francis')
            ->setLastName('AHANDA')
            ->setPassword($this->encoder->encodePassword($adminUser, 'password'))
            ->setRoles(['ROLE_ADMIN'])
            ->setEnterprise($enterprise)
            //->setVerified(true)
            ->setPhoneNumber('695385802')
            ->setCountryCode('+237');
        $manager->persist($adminUser);

        //Site de Douala du client SATC
        $siteDouala = new Site();

        $siteDouala->setName('Douala')
            ->setCurrency('XAF')
            ->setEnterprise($enterprise);

        $manager->persist($siteDouala);

        $smartMod = new SmartMod();
        $modType = 'FUEL';
        //$instaType = $faker->randomElement($instaTypes);
        $nameMod = 'Livraison Groupe Electrogène';

        $smartMod->setModuleId($faker->unique()->randomNumber($nbDigits = 8, $strict = false))
            ->setSite($siteDouala)
            ->setModType($modType)
            ->setFuelPrice(575)
            ->setName($nameMod);

        $manager->persist($smartMod);

        $manager->flush();
    }
}
