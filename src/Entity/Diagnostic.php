<?php

namespace App\Entity;

use App\Repository\DiagnosticRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\UX\Turbo\Attribute\Broadcast;

#[ORM\Entity(repositoryClass: DiagnosticRepository::class)]
#[Broadcast]
class Diagnostic
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['api_user'])]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?float $totalStress = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(inversedBy: 'diagnostics')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    /**
     * @var Collection<int, Reponse>
     */
    #[ORM\OneToMany(targetEntity: Reponse::class, mappedBy: 'diagnostics')]
    private Collection $reponses;

    public function __construct()
    {
        $this->reponses = new ArrayCollection();
        $this->dateCreation = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTotalStress(): ?float
    {
        return $this->totalStress;
    }

    public function setTotalStress(?float $totalStress): static
    {
        $this->totalStress = $totalStress;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(): static
    {
        $dateCreation = new \DateTime('now');

        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    /**
     * @return Collection<int, Reponse>
     */
    public function getReponses(): Collection
    {
        return $this->reponses;
    }

    public function addReponse(Reponse $reponse): static
    {
        if (!$this->reponses->contains($reponse)) {
            $this->reponses->add($reponse);
            $reponse->setDiagnostics($this);
        }

        return $this;
    }

    public function removeReponse(Reponse $reponse): static
    {
        if ($this->reponses->removeElement($reponse)) {
            // set the owning side to null (unless already changed)
            if ($reponse->getDiagnostics() === $this) {
                $reponse->setDiagnostics(null);
            }
        }

        return $this;
    }

    public function getNiveauStress(int $totalStress): string
    {
        if ($totalStress >= 300) {
            return 'Élevé';
        } elseif ($totalStress >= 100 && $totalStress < 300) {
            return 'Modéré';
        } else {
            return 'Faible';
        }
    }

    public function getCommentaire(int $totalStress): ?string
    {
        $commentaire = '';

        if ($totalStress >= 300) {
            $commentaire .= '<div class="stress-score-container high-stress">
                        <h2>Plus de 300 points : Stress très élevé</h2>
                        <p><strong>Risque évalué à 80 %</strong></p>
                        <p>
                            Si votre score de stress vécu au cours des 24 derniers mois dépasse <strong>300</strong>,
                            vos risques de présenter dans un avenir proche une maladie somatique sont <strong>très élevés</strong>.
                        </p>
                        <p>
                            Un score de <strong>300 et plus</strong> suppose que vous avez eu à traverser une série de situations particulièrement pénibles et éprouvantes.
                            Ne craignez donc pas de vous faire aider si c\'est votre cas.
                        </p>';
        } elseif ($totalStress >= 100 && $totalStress < 300) {
            $commentaire .= '<div class="stress-score-container medium-stress">
                        <h2>Entre 100 et 300 points : Stress élevé</h2>
                        <p><strong>Risque évalué à 51 %</strong></p>
                        <p>
                            Ces risques diminuent en même temps que votre score total. Toutefois, si votre score est compris entre 300 et 100,
                            les risques qu\'une maladie somatique se déclenche demeurent statistiquement significatifs.
                        </p>
                        <p>
                            Prenez soin de vous. Ce n\'est pas la peine d\'en rajouter.
                        </p>';
        } elseif ($totalStress < 100) {
            $commentaire .= '<div class="stress-score-container low-stress">
                        <h2>Moins de 100 points : Stress modéré</h2>
                        <p><strong>Risque évalué à 30 %</strong></p>
                        <p>
                            En dessous de 100, le risque se révèle peu important. La somme des stress rencontrés est trop faible
                            pour ouvrir la voie à une maladie somatique.
                        </p>';
        }

        $commentaire .= '</div>';

        return $commentaire;
    }
}
