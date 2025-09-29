<?php
//
//namespace App\Tests\Controller;
//
//use App\Entity\Role;
//use App\Entity\Utilisateur;
//use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
//use Symfony\Component\HttpFoundation\File\UploadedFile;
//
//class SecurityControllerTest extends WebTestCase
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
//
//        $this->truncateEntities([Utilisateur::class, Role::class]);
//
//        $role = new Role();
//        $role->setNom('ROLE_USER');
//        $this->entityManager->persist($role);
//        $this->entityManager->flush();
//    }
//
//    //Test d'intégration
//    public function testSignup(): void
//    {
//        $crawler = $this->client->request('GET', '/signup');
//        $this->assertResponseIsSuccessful();
//
//        // Créer un fichier temporaire pour la photo de profil
//        $tmpFile = tempnam(sys_get_temp_dir(), 'test_');
//        copy(__DIR__ . '/../../public/uploads/profiles/profilePicture1.png', $tmpFile);
//        $photo = new UploadedFile(
//            $tmpFile,
//            'test.png',
//            'image/png',
//            null,
//            true
//        );
//
//        // Soumettre le formulaire directement
//        $this->client->submitForm("S'inscrire", [
//            'signup[email]' => 'nouveau@example.com',
//            'signup[password]' => 'Password@123',
//            'signup[confirmPassword]' => 'Password@123',
//            'signup[nom]' => 'Nouveau',
//            'signup[prenom]' => 'Utilisateur',
//            'signup[username]' => 'newuser',
//            'signup[dateNaissance]' => '1990-01-01',
//            'signup[profilePicture]' => $photo,
//        ]);
//
//        // Vérifier la redirection
//        $this->assertResponseRedirects('/');
//
//        // Suivre la redirection
//        $this->client->followRedirect();
//        $this->assertResponseIsSuccessful();
//
//        // Vérifications en base de données
//        $user = $this->entityManager->getRepository(Utilisateur::class)
//            ->findOneBy(['email' => 'nouveau@example.com']);
//
//        // Assertions plus complètes
//        $this->assertNotNull($user);
//        $this->assertEquals('Nouveau', $user->getNom());
//        $this->assertEquals('Utilisateur', $user->getPrenom());
//        $this->assertEquals('newuser', $user->getUsername());
//        $this->assertTrue(password_verify('Password@123', $user->getPassword()));
//        $this->assertNotNull($user->getPhotoProfile());
//        $this->assertTrue($user->isActif());
//
//        unlink($tmpFile);
//    }
//
//    // test fonctionnel
//    public function testLogin(): void
//    {
//        $userRepository = $this->entityManager->getRepository(Utilisateur::class);
//        $roleRepository = $this->entityManager->getRepository(Role::class);
//
//        $testUser = new Utilisateur();
//        $testUser->setEmail('test@test.com');
//        $testUser->setPassword(password_hash('password123', PASSWORD_BCRYPT));
//        $testUser->setRole($roleRepository->findOneBy(['nom' => 'ROLE_USER']));
//        $testUser->setNom('Test');
//        $testUser->setPrenom('User');
//        $testUser->setIsActif(true);
//        $testUser->setDateNaissance(new \DateTime('1990-01-01'));
//
//        $this->entityManager->persist($testUser);
//        $this->entityManager->flush();
//
//        $crawler = $this->client->request('GET', '/login');
//        $this->assertResponseIsSuccessful();
//
//        $this->client->submitForm('Se connecter', [
//            '_username' => 'test@test.com',
//            '_password' => 'password123',
//        ]);
//
//        $this->assertResponseRedirects('/');
//
//        $this->client->followRedirect();
//        $this->assertResponseIsSuccessful();
//    }
//
//    public function testForgotPassword(): void
//    {
//        // Préparer un utilisateur valide
//        $user = new Utilisateur();
//        $user->setEmail('test@example.com');
//        $user->setPassword('password');
//        $user->setNom('Test');
//        $user->setPrenom('User');
//        $user->setIsActif(true);
//        $user->setDateNaissance(new \DateTime('1990-01-01'));
//        $user->setRole($this->entityManager->getRepository(Role::class)->findOneBy(['nom' => 'ROLE_USER']));
//        $this->entityManager->persist($user);
//        $this->entityManager->flush();
//
//        $crawler = $this->client->request('GET', '/request-reset-password');
//        $this->assertResponseIsSuccessful();
//
//        // Soumettre l'email existant
//        $this->client->submitForm('Envoyer', [
//            'request_forgot_password[mail]' => 'test@example.com'
//        ]);
//
//        $this->assertResponseRedirects();
//    }
//
//
//    private function truncateEntities(array $entities): void
//    {
//        $connection = $this->entityManager->getConnection();
//        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');
//
//        foreach ($entities as $entity) {
//            $metadata = $this->entityManager->getClassMetadata($entity);
//            $tableName = $metadata->getTableName();
//            $connection->executeStatement("TRUNCATE TABLE {$tableName}");
//        }
//
//        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
//    }
//}