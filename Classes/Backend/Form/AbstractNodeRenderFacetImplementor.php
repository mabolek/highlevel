<?php

declare(strict_types=1);

namespace Mabolek\Highlevel\Backend\Form;

use Mabolek\Highlevel\Instruction\InstructionRegistry;
use TYPO3\CMS\Backend\Form\AbstractNode;

class AbstractNodeRenderFacetImplementor extends AbstractNode
{
    public function render(): array
    {
        return (new InstructionRegistry())
            ->getInstruction($this->data['renderType'])
            ->abstractNodeRenderFacet($this->data);
    }
}
