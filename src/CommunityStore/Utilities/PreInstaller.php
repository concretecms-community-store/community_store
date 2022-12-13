<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use Concrete\Core\Page\Template as PageTemplateList;
use Concrete\Core\Entity\Page\Template as PageTemplate;

class PreInstaller
{
    public function getPageTemplates(): array
    {
        $array = [];
        $pageTemplates = PageTemplateList::getList();

        /** @var PageTemplate[] $pageTemplates */
        foreach ($pageTemplates as $pageTemplate) {
            $array[$pageTemplate->getPageTemplateID()] = $pageTemplate->getPageTemplateDisplayName();
        }

        return $array;
    }
}
