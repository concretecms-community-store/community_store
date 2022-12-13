<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Entity;

use Doctrine\ORM\Mapping as ORM;
use Concrete\Package\CommunityStore\Src\CommunityStore\Repository\TaxClassRepository;

/**
 * @ORM\Entity(repositoryClass=TaxClassRepository::class)
 * @ORM\Table(name="CommunityStoreTaxClasses")
 */
class TaxClass
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected ?int $id = null;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    protected string $handle;

    /**
     * @ORM\Column(type="string")
     */
    protected string $name;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    protected ?string $rates = null;

    /**
     * @ORM\Column(type="boolean")
     */
    protected bool $locked;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHandle(): string
    {
        return $this->handle;
    }

    public function setHandle(string $handle): self
    {
        $this->handle = $handle;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getRates(): ?string
    {
        return $this->rates;
    }

    public function setRates(?string $rates): self
    {
        $this->rates = $rates;

        return $this;
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function setLocked(bool $locked): self
    {
        $this->locked = $locked;

        return $this;
    }
}
