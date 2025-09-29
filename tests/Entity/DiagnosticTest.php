<?php
//
//namespace App\Tests\Entity;
//
//use App\Entity\Contenu;
//use App\Entity\Diagnostic;
//use App\Entity\ResetPassword;
//use App\Entity\Role;
//use App\Entity\Utilisateur;
//use PHPUnit\Framework\TestCase;
//
//class DiagnosticTest extends TestCase
//{
//    private Diagnostic $diagnostic;
//
//    protected function setUp(): void
//    {
//        $this->diagnostic = new Diagnostic();
//    }
//
//    public function testTotalStressAssignment(): void
//    {
//        $totalStress = 75.5;
//        $this->diagnostic->setTotalStress($totalStress);
//        $this->assertEquals($totalStress, $this->diagnostic->getTotalStress());
//    }
//
//    //Test unitaire
//    public function testCommentaireAssignment(): void
//    {
//        $diagnostic = new Diagnostic();
//
//        $commentaireFaible = $diagnostic->getCommentaire(50);
//        $this->assertStringContainsString('Stress modéré', $commentaireFaible);
//        $this->assertStringContainsString('30 %', $commentaireFaible);
//
//        $commentaireModere = $diagnostic->getCommentaire(150);
//        $this->assertStringContainsString('Stress élevé', $commentaireModere);
//        $this->assertStringContainsString('51 %', $commentaireModere);
//
//        $commentaireEleve = $diagnostic->getCommentaire(350);
//        $this->assertStringContainsString('Stress très élevé', $commentaireEleve);
//        $this->assertStringContainsString('80 %', $commentaireEleve);
//    }
//}