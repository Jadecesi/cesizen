<?php

namespace App\Tests\Entity;

use App\Entity\Contenu;
use App\Entity\Diagnostic;
use App\Entity\ResetPassword;
use App\Entity\Role;
use App\Entity\Utilisateur;
use PHPUnit\Framework\TestCase;

class UtilisateurTest extends TestCase
{
    private Utilisateur $utilisateur;

    protected function setUp(): void
    {
        $this->utilisateur = new Utilisateur();
    }

    public function testEmailAssignment(): void
    {
        $email = 'test@example.com';
        $this->utilisateur->setEmail($email);
        $this->assertEquals($email, $this->utilisateur->getEmail());
    }

    public function testNomPrenomAssignment(): void
    {
        $nom = 'Dupont';
        $prenom = 'Jean';

        $this->utilisateur->setNom($nom);
        $this->utilisateur->setPrenom($prenom);

        $this->assertEquals($nom, $this->utilisateur->getNom());
        $this->assertEquals($prenom, $this->utilisateur->getPrenom());
    }

    public function testAgeCalculation(): void
    {
        $dateNaissance = new \DateTime('20 years ago');
        $this->utilisateur->setDateNaissance($dateNaissance);

        $this->assertEquals(20, $this->utilisateur->getAge());
    }

    public function testDefaultRole(): void
    {
        $this->assertEquals('ROLE_USER', $this->utilisateur->getRoles()[0]);
    }

    public function testIsActifToggle(): void
    {
        $this->utilisateur->setIsActif(true);
        $this->assertTrue($this->utilisateur->isActif());

        $this->utilisateur->setIsActif(false);
        $this->assertFalse($this->utilisateur->isActif());
    }

    public function testPasswordAssignment(): void
    {
        $password = 'secure_hash';
        $this->utilisateur->setPassword($password);
        $this->assertEquals($password, $this->utilisateur->getPassword());
    }

    public function testUsernameFallbackToEmail(): void
    {
        $email = 'fallback@example.com';
        $this->utilisateur->setEmail($email);

        $this->assertEquals($email, $this->utilisateur->getUserIdentifier());

        $this->utilisateur->setUsername('customUsername');
        $this->assertEquals('customUsername', $this->utilisateur->getUserIdentifier());
    }

    public function testCustomRoleAssignment(): void
    {
        $role = new Role();
        $role->setNom('ROLE_ADMIN');
        $this->utilisateur->setRole($role);

        $this->assertEquals(['ROLE_ADMIN'], $this->utilisateur->getRoles());
    }

    public function testTokenAndExpiration(): void
    {
        $token = '123abc';
        $expiresAt = new \DateTimeImmutable('+1 day');

        $this->utilisateur->setApiToken($token);
        $this->utilisateur->setTokenExpiresAt($expiresAt);

        $this->assertEquals($token, $this->utilisateur->getApiToken());
        $this->assertEquals($expiresAt, $this->utilisateur->getTokenExpiresAt());
    }

    public function testAddRemoveDiagnostic(): void
    {
        $diagnostic = new Diagnostic();
        $this->utilisateur->addDiagnostic($diagnostic);

        $this->assertTrue($this->utilisateur->getDiagnostics()->contains($diagnostic));

        $this->utilisateur->removeDiagnostic($diagnostic);
        $this->assertFalse($this->utilisateur->getDiagnostics()->contains($diagnostic));
    }

    public function testAddRemoveContenu(): void
    {
        $contenu = new Contenu();
        $this->utilisateur->addContenu($contenu);

        $this->assertTrue($this->utilisateur->getContenus()->contains($contenu));

        $this->utilisateur->removeContenu($contenu);
        $this->assertFalse($this->utilisateur->getContenus()->contains($contenu));
    }

    public function testAddRemoveResetPassword(): void
    {
        $reset = new ResetPassword();
        $this->utilisateur->addResetPassword($reset);

        $this->assertTrue($this->utilisateur->getResetPasswords()->contains($reset));

        $this->utilisateur->removeResetPassword($reset);
        $this->assertFalse($this->utilisateur->getResetPasswords()->contains($reset));
    }

}