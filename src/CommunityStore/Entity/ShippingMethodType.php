<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Entity;

use Concrete\Core\Entity\Package;
use Doctrine\ORM\Mapping as ORM;
use Concrete\Package\CommunityStore\Src\CommunityStore\Repository\ShippingMethodTypeRepository;

/**
 * @ORM\Entity(repositoryClass=ShippingMethodTypeRepository::class)
 * @ORM\Table(name="CommunityStoreShippingMethodTypes1")
*/
class ShippingMethodType
{
    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string")
     */
    private string $handle;

    /**
     * @ORM\Column(type="string")
     */
    private string $name;

    /**
     * @ORM\ManyToOne(targetEntity="Concrete\Core\Entity\Package", inversedBy="Packages", cascade={"persist"})
     * @ORM\JoinColumn(referencedColumnName="pkgID", onDelete="CASCADE")
     */
    private Package $package;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $hideFromAddMenu = null;

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

    public function getPackage(): Package
    {
        return $this->package;
    }


    public function setPackage(Package $package): self
    {
        $this->package = $package;

        return $this;
    }

    public function getHideFromAddMenu(): ?int
    {
        return $this->hideFromAddMenu;
    }

    public function setHideFromAddMenu(?int $hideFromAddMenu): self
    {
        $this->hideFromAddMenu = $hideFromAddMenu;

        return $this;
    }
}

