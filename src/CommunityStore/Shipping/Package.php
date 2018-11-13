<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Shipping;

class Package
{
    protected $weight;
    protected $length;
    protected $width;
    protected $height;

    /**
     * @ORM\return mixed
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @ORM\param mixed $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * @ORM\return mixed
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @ORM\param mixed $length
     */
    public function setLength($length)
    {
        $this->length = $length;
    }

    /**
     * @ORM\return mixed
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @ORM\param mixed $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * @ORM\return mixed
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @ORM\param mixed $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }
}
