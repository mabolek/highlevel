<?php

declare(strict_types=1);

namespace Mabolek\Highlevel\Instruction\ImplementationFacet;

use TYPO3\CMS\Backend\Form\AbstractNode;

interface AbstractNodeRenderFacet
{
    public function abstractNodeRenderFacet(array $data): array;
}
