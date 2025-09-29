<?php
//
//namespace App\Tests\Controller;
//
//use App\Entity\Role;
//use App\Entity\Contenu;
//use App\Entity\Utilisateur;
//use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
//use Symfony\Component\HttpFoundation\File\UploadedFile;
//use Doctrine\ORM\EntityManagerInterface;
//use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
//
//class ContenuTest extends WebTestCase
//{
//    private $client;
//    private $entityManager;
//
//    protected function setUp(): void
//    {
//        $this->client = static::createClient();
//        $this->entityManager = $this->client->getContainer()
//            ->get('doctrine')
//            ->getManager();
//        $this->passwordHasher = $this->client->getContainer()
//            ->get(UserPasswordHasherInterface::class);
//
//        // Nettoyer la base de données
//        $this->cleanDatabase();
//
//        // Créer le rôle admin
//        $roleAdmin = new Role();
//        $roleAdmin->setNom("ROLE_ADMIN");
//        $this->entityManager->persist($roleAdmin);
//
//        // Créer un utilisateur admin
//        $userAdmin = new Utilisateur();
//        $userAdmin->setEmail('admin@test.com');
//        $userAdmin->setRole($roleAdmin);
//        $userAdmin->setNom('admin');
//        $userAdmin->setPrenom('admin');
//        $userAdmin->setIsActif(true);
//        $userAdmin->setPhotoProfile('profilePicture2.png');
//        $userAdmin->setDateNaissance(new \DateTime('1990-01-01'));
//
//        $hashedPassword = $this->passwordHasher->hashPassword($userAdmin, 'adminpass');
//        $userAdmin->setPassword($hashedPassword);
//
//        $this->entityManager->persist($userAdmin);
//        $this->entityManager->flush();
//    }
//
//    private function cleanDatabase(): void
//    {
//        $connection = $this->entityManager->getConnection();
//
//        // Désactiver les contraintes de clés étrangères
//        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');
//
//        // Nettoyer les tables
//        $connection->executeStatement('TRUNCATE TABLE contenu');
//        $connection->executeStatement('TRUNCATE TABLE utilisateur');
//        $connection->executeStatement('TRUNCATE TABLE role');
//
//        // Réactiver les contraintes
//        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
//    }
//
//    //Test d'intégration pour l'ajout de contenu
//    public function testAjoutContenu(): void
//    {
//        $userRepository = $this->entityManager->getRepository(Utilisateur::class);
//        $user = $userRepository->findOneBy(['email' => 'admin@test.com']);
//
//        $this->client->loginUser($user);
//
//        $crawler = $this->client->request('GET', '/contenu/admin/new');
//        $this->assertResponseIsSuccessful();
//
//        $imagePath = __DIR__.'/../../public/uploads/profiles/profilePicture1.png';
//        $image = new UploadedFile(
//            $imagePath,
//            'profilePicture1.png',
//            'image/png',
//            null,
//            true
//        );
//
//        $form = $crawler->selectButton('Ajouter')->form([
//            'contenu[titre]' => 'Test Contenu',
//            'contenu[image]' => $image,
//            'contenu[description]' => 'Description test',
//            'contenu[url]' => 'https://test.com'
//        ]);
//
//        $this->client->submit($form);
//
//        $this->assertResponseRedirects('/contenu/');
//
//        // Vérifier que le contenu a été créé
//        $contenu = $this->entityManager->getRepository(Contenu::class)
//            ->findOneBy(['titre' => 'Test Contenu']);
//
//        $this->assertNotNull($contenu);
//        $this->assertEquals('Description test', $contenu->getDescription());
//
//        if ($contenu && $contenu->getImage()) {
//            $imagePath = $this->client->getContainer()->getParameter('contenu_pictures_directory').'/'.$contenu->getImage();
//            if (file_exists($imagePath)) {
//                unlink($imagePath);
//            }
//        }
//    }
//
//    protected function tearDown(): void
//    {
//        parent::tearDown();
//        $this->entityManager->close();
//        $this->entityManager = null;
//    }
//}