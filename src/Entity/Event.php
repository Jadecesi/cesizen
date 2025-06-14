<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\UX\Turbo\Attribute\Broadcast;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['api_diagnostic', 'api_event'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['api_event', 'api_diagnostic'])]
    private ?string $nom = null;

    #[ORM\Column]
    #[Groups(['api_event'])]
    private ?int $stress = null;

    #[ORM\ManyToMany(targetEntity: Diagnostic::class, mappedBy: 'events')]
    #[Groups(['api_event'])]
    private Collection $diagnostics;

    #[ORM\ManyToOne(inversedBy: 'events')]
    private ?CategorieEvent $categorie = null;

    public function __construct()
    {
        $this->diagnostics = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getStress(): ?int
    {
        return $this->stress;
    }

    public function setStress(int $stress): static
    {
        $this->stress = $stress;

        return $this;
    }

    /**
     * @return Collection<int, Diagnostic>
     */
    public function getDiagnostics(): Collection
    {
        return $this->diagnostics;
    }

    public function addDiagnostic(Diagnostic $diagnostic): static
    {
        if (!$this->diagnostics->contains($diagnostic)) {
            $this->diagnostics->add($diagnostic);
        }
        return $this;
    }

    public function removeDiagnostic(Diagnostic $diagnostic): static
    {
        $this->diagnostics->removeElement($diagnostic);
        return $this;
    }

    public function getCategorie(): ?CategorieEvent
    {
        return $this->categorie;
    }

    public function setCategorie(?CategorieEvent $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }
}