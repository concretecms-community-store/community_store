<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Clerk;

class ClerkItem implements \DVDoug\BoxPacker\Item
{
    public function __construct($aDescription, $aWidth, $aLength, $aDepth, $aWeight)
    {
        $this->description = $aDescription;
        $this->width = $aWidth;
        $this->length = $aLength;
        $this->depth = $aDepth;
        $this->weight = $aWeight;
        $this->volume = $this->width * $this->length * $this->depth;
    }
    public function getDescription()
    {
        return $this->description;
    }
    public function getWidth()
    {
        return $this->width;
    }
    public function getLength()
    {
        return $this->length;
    }
    public function getDepth()
    {
        return $this->depth;
    }
    public function getWeight()
    {
        return $this->weight;
    }
    public function getVolume()
    {
        return $this->volume;
    }
}
