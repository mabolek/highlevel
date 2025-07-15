<?php

declare(strict_types=1);


namespace Mabolek\Highlevel\FormEngine\FieldControl;


use Mabolek\Highlevel\Instruction\FieldButton\FieldButtonInstruction;
use Mabolek\Highlevel\Instruction\InstructionProvider;
use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FieldButtonControl extends AbstractNode
{
    public function render(): array
    {
        /** @var FieldButtonInstruction $instruction */
        $instruction = GeneralUtility::makeInstance(InstructionProvider::class)
            ->getByIdentifier($this->data["renderType"]);

        $icon = $instruction->getIcon();

        return [
            'iconIdentifier' => $icon instanceof Icon ? $icon->getIdentifier() : $icon,
            'title' => $instruction->getLabel(),
            'linkAttributes' => [
                'class' => 'highLevelFieldButton ',
                'data-highlevel-identifier' => $instruction->getIdentifier(),
                'data-formengine-input-name' => $this->data['elementBaseName'],
                'data-formengine-output-name' => $this->data['elementBaseName'],
            ],
            'javaScriptModules' => [JavaScriptModuleInstruction::create('@mabolek/highlevel/FieldButton.js')],
        ];
    }
}