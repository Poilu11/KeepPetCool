<?php

namespace App\DataFixtures;

use DateTime;
use Faker\Factory;
use App\Entity\Role;
use App\Entity\User;
use App\Util\Slugger;
use App\Entity\Comment;
use App\Entity\Service;
use App\Entity\Species;
use App\Entity\Presentation;
use Faker\ORM\Doctrine\Populator;
use App\DataFixtures\Faker\NoteProvider;
use App\DataFixtures\Faker\SpeciesProvider;
use Doctrine\Bundle\FixturesBundle\Fixture;
use App\DataFixtures\Faker\UserTypeProvider;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    private $slugger;

    public function __construct(UserPasswordEncoderInterface $encoder, Slugger $slugger)
    {
        // Récupération du service d'encodement des mots de passe
        $this->encoder = $encoder;

        // Récupération du service Slugger
        $this->slugger = $slugger;
    }

    public function load(ObjectManager $em)
    {
        $adminRole = new Role();
        $adminRole->setCode('ROLE_ADMIN');
        $adminRole->setName('Administrateur');

        $em->persist($adminRole);

        $modoRole = new Role();
        $modoRole->setCode('ROLE_MODO');
        $modoRole->setName('Modérateur');

        $em->persist($modoRole);

        $userRole = new Role();
        $userRole->setCode('ROLE_USER');
        $userRole->setName('Utilisateur');

        $em->persist($userRole);

        $adminUser = new User();
        $adminUser->setUsername('admin')
            ->setPassword($this->encoder->encodePassword($adminUser, 'admin'))
            ->setEmail('admin@admin.com')
            ->setType('administrateur')
            ->setFirstname('Toto')
            ->setLastname('Lunar')
            ->setAvatar('default-avatar.png')
            ->setAddress('10 rue Tatamine')
            ->setZipCode('75000')
            ->setCity('Paris')
            ->setLongitude(4.079306)
            ->setLatitude(48.293024)
            ->setIsValidated(false)
            ->setCreatedAt(new DateTime())
            ->setRole($adminRole);
        
        $em->persist($adminUser);

        $modoUser = new User();
        $modoUser->setUsername('modo')
            ->setPassword($this->encoder->encodePassword($modoUser, 'modo'))
            ->setEmail('modo@modo.com')
            ->setType('moderateur')
            ->setFirstname('Tata')
            ->setLastname('Lunar')
            ->setAvatar('default-avatar.png')
            ->setAddress('10 rue Tatamane')
            ->setZipCode('73000')
            ->setCity('Chambéry')
            ->setLongitude(4.079306)
            ->setLatitude(48.293024)
            ->setIsValidated(false)
            ->setCreatedAt(new DateTime())
            ->setRole($modoRole);
        
        $em->persist($modoUser);

        $petsitterUser = new User();
        $petsitterUser->setUsername('petsit')
            ->setPassword($this->encoder->encodePassword($petsitterUser, 'petsit'))
            ->setEmail('petuser@user.com')
            ->setType('petsitter')
            ->setFirstname('Titi')
            ->setLastname('PetsitterFamily')
            ->setAvatar('default-avatar.png')
            ->setAddress('17 rue Blibli')
            ->setZipCode('74000')
            ->setCity('Annecy')
            ->setLongitude(4.079306)
            ->setLatitude(48.293024)
            ->setIsValidated(false)
            ->setCreatedAt(new DateTime())
            ->setRole($userRole);
        
        $em->persist($petsitterUser);

        $ownerUser = new User();
        $ownerUser->setUsername('owner')
            ->setPassword($this->encoder->encodePassword($ownerUser, 'owner'))
            ->setEmail('owneruser@user.com')
            ->setType('owner')
            ->setFirstname('GrosMinet')
            ->setLastname('OwnerFamily')
            ->setAvatar('default-avatar.png')
            ->setAddress('10 rue Blabla')
            ->setZipCode('74000')
            ->setCity('Albertville')
            ->setLongitude(4.079306)
            ->setLatitude(48.293024)
            ->setIsValidated(false)
            ->setCreatedAt(new DateTime())
            ->setRole($userRole);
        
        $em->persist($ownerUser);

        $service1 = new Service();
        $service1->setName('Gardiennage à domicile');

        $em->persist($service1);
        
        $service2 = new Service();
        $service2->setName('Pension');

        $em->persist($service2);

        $service3 = new Service();
        $service3->setName('Promenade');

        $em->persist($service3);

        $service4 = new Service();
        $service4->setName('Transport');

        $em->persist($service4);

        $service5 = new Service();
        $service5->setName('Visite');

        $em->persist($service5);
        
        // On enregistre toutes les nouvelles entrées en BDD
        $em->flush();

        // Utilisation de Faker

        $generator = Factory::create('fr_FR');

        // Ajout ProviderFaker
        $generator->addProvider(new SpeciesProvider($generator));
        $generator->addProvider(new NoteProvider($generator));
        $generator->addProvider(new UserTypeProvider($generator));
        
        $populator = new Populator($generator, $em);
        $populator->addEntity(User::class, 70, array(
            'type' => function() use ($generator) { return $generator->randomUserType(); },
            'firstname' => function() use ($generator) { return $generator->firstName(null); },
            'lastname' => function() use ($generator) { return $generator->lastName; },
            'phoneNumber' => function() use ($generator) { return $generator->e164PhoneNumber; },
            'cellNumber' => function() use ($generator) { return $generator->e164PhoneNumber; },
            'avatar' => 'default-avatar.png',
            'isActive' => true,
            'isValidated' => false,
            'role' => $userRole,
        ));

        $populator->addEntity(Species::class, 5, array(
            'name' => function() use ($generator) { return $generator->unique()->speciesName(); },
          ));
       
        $populator->addEntity(Presentation::class, 50, array(
          'title' => function() use ($generator) { return $generator->words($nb = 7, $asText = true); },
          'body' => function() use ($generator) { return $generator->sentence($nbWords = 100, $variableNbWords = true); },
          'isActive' => true,
        ), [
            // https://github.com/fzaninotto/Faker#populating-entities-using-an-orm-or-an-odm
             function($presentation) {
                $presentation->setSlug(
                    $this->slugger->sluggify(
                        $presentation->getTitle()
                    )
                );
            }
        ]);

        $populator->addEntity(Comment::class, 40, array(
            'body' => function() use ($generator) { return $generator->sentence($nbWords = 50, $variableNbWords = true); },
            'note' => function() use ($generator) { return $generator->randomNote(); },
            'isActive' => true,
            'isValidated' => false,
            'isDisplayed' => true
          ));
        
        $insertedEntities = $populator->execute();

        dump($insertedEntities);

        // Gestion des relations ManyToMany
        // car non gérées par Faker

        $presentationArray = $insertedEntities['App\Entity\Presentation'];
        $speciesArray = $insertedEntities['App\Entity\Species'];
        $serviceArray = [
            $service1,
            $service2,
            $service3,
            $service4,
            $service5
        ];

        // Ajout d'une ou plusieurs espèces par présentation
        foreach($presentationArray as $currentPresentation)
        {
            shuffle($speciesArray);

            $randomNb = mt_rand(1, 5);

            for($i = 0; $i < $randomNb; $i++)
            {    
                $currentPresentation->addSpecies($speciesArray[$i]);
                $em->persist($currentPresentation);
            }
        }

        // Ajout d'un ou plusieurs services par présentation
        foreach($presentationArray as $currentPresentation)
        {
            shuffle($serviceArray);

            $randomNb = mt_rand(1, 3);

            for($i = 0; $i < $randomNb; $i++)
            {    
                $currentPresentation->addService($serviceArray[$i]);
                $em->persist($currentPresentation);
            }
        }

        $em->flush();
   
    }
}
