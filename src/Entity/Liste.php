<?php

namespace App\Entity;

use App\Repository\ListeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ListeRepository::class)]
class Liste
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'listes')]
    private ?User $owner = null;

    #[ORM\ManyToOne(inversedBy: 'listes')]
    private ?Article $article = null;

    #[ORM\Column]
    private ?bool $okay = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): static
    {
        $this->article = $article;

        return $this;
    }

    public function isOkay(): ?bool
    {
        return $this->okay;
    }

    public function setOkay(bool $okay): static
    {
        $this->okay = $okay;

        return $this;
    }
}
