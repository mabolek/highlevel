<?php

declare(strict_types=1);

namespace Mabolek\Highlevel\Instruction;

use Mabolek\Highlevel\Backend\Form\AbstractNodeRenderFacetImplementor;
use Mabolek\Highlevel\Instruction\Callback\GenericStringToStringCallback;
use Mabolek\Highlevel\Instruction\Callback\GenericToArrayOfStringsCallback;
use Mabolek\Highlevel\Instruction\Callback\GenericToStringCallback;
use Mabolek\Highlevel\Instruction\ImplementationFacet\AbstractNodeRenderFacet;
use Mabolek\Highlevel\Instruction\ImplementationFacet\AjaxControllerFacet;
use Mabolek\Highlevel\Instruction\ImplementationFacet\ExtLocalconfFacet;
use Mabolek\Highlevel\Instruction\ImplementationFacet\IconsFacet;
use Mabolek\Highlevel\Instruction\ImplementationFacet\TcaOverridesFacet;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class FieldButtonInstruction extends AbstractInstruction implements ExtLocalconfFacet, IconsFacet, TcaOverridesFacet, AjaxControllerFacet, AbstractNodeRenderFacet
{
    private ?string $iconIdentifier = null;

    /**
     * @param string|\Closure|GenericToStringCallback $label The title for the button.
     * @param string|\Closure|GenericToStringCallback $icon Path to the icon file or icon identifier for the icon shown on the button.
     * @param string|array|callable-string|\Closure():string[]|GenericToArrayOfStringsCallback $table Table(s) to apply the button to.
     * @param string|array|callable-string|\Closure():string[]|GenericToArrayOfStringsCallback $sourceField The field to pick the value from.
     * @param string|array|\Closure|GenericStringToStringCallback $callback The callback function. Processes the supplied input value.
     * @param string|array|callable-string|\Closure():string[]|GenericToArrayOfStringsCallback|null $targetField Optional field to show the button next to and write the value to. Otherwise, the source field is used.
     */
    public function __construct(
        protected string|\Closure|GenericToStringCallback $label,
        protected string|\Closure|GenericToStringCallback $icon,
        protected string|array|\Closure|GenericToArrayOfStringsCallback $table,
        protected string|array|\Closure|GenericToArrayOfStringsCallback $sourceField,
        protected string|array|\Closure|GenericStringToStringCallback $callback,
        protected string|array|\Closure|GenericStringToStringCallback|null $targetField = null,
    ) {}

    public function __invoke(string $value): string
    {
        $callbackChain = $this->callback;

        if (!is_array($callbackChain)) {
            $callbackChain = [$callbackChain];
        }

        foreach ($callbackChain as $callback) {
            $value = ($this->initializeCallable($callback))($value);
        }

        return $value;
    }

    public function getIcon(): string
    {
        return $this->forgivingBind($this->icon)();
    }

    public function getLabel(): string
    {
        return $this->forgivingBind($this->label)();
    }

    /**
     * @return string[] table names
     */
    public function getTables(): array
    {
        return (array)$this->forgivingBind($this->table)();
    }

    /**
     * @return string[] field names
     */
    public function getSourceFields(): array
    {
        return (array)$this->forgivingBind($this->sourceField)();
    }

    /**
     * @return string[] field names
     */
    public function getTargetFields(): array
    {
        if ($this->targetField === null) {
            return $this->getSourceFields();
        }

        return (array)$this->forgivingBind($this->targetField)();
    }

    public function extLocalconfFacet(): void
    {
        $nextRegistrySlot = max(array_keys($GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'])) + 1;

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][$nextRegistrySlot] = [
            'nodeName' => $this->getIdentifier(),
            'priority' => 30,
            'class' => AbstractNodeRenderFacetImplementor::class,
        ];
    }

    public function iconsFacet(): array
    {
        $icon = $this->getIcon();

        $iconIdentifier = $this->getIconIdentifier($icon);

        // If the icon references an existing icon identifier, we don't need to register a new icon.
        if ($iconIdentifier !== $this->getIdentifier()) {
            return [];
        }

        $iconProvider = BitmapIconProvider::class;

        if (str_ends_with($this->getIcon(), '.svg')) {
            $iconProvider = SvgIconProvider::class;
        }

        return [
            $iconIdentifier => [
                'provider' => $iconProvider,
                'source' => $icon,
            ]
        ];
    }

    public function tcaOverridesFacet(): void
    {
        foreach ($this->getTables() as $table) {
            $instanceWithSingleTable = new self(
                label: $this->label,
                icon: $this->icon,
                table: $table,
                sourceField: $this->sourceField,
                callback: $this->callback,
                targetField: $this->targetField,
            );
            $instanceWithSingleTable->setIdentifier($this->getIdentifier());
            $instanceWithSingleTable->setPackage($this->getPackage());

            foreach ($instanceWithSingleTable->getTargetFields() as $field) {
                if (!isset($GLOBALS['TCA'][$table]['columns'][$field])) {
                    continue;
                }

                $GLOBALS['TCA'][$table]['columns'][$field]['config']['fieldControl'][
                $instanceWithSingleTable->getIdentifier()
                ] = [
                    'renderType' => $instanceWithSingleTable->getIdentifier(),
                ];
            }
        }

        // TODO: Consider caching at this point.
    }

    public function ajaxControllerFacet(array $data): array
    {
        $value = $this($data['value']);

        return [
            'value' => $value,
        ];
    }

    public function abstractNodeRenderFacet(array $data): array
    {
        return [
            'iconIdentifier' => $this->getIconIdentifier(),
            'title' => $this->getLabel(),
            'linkAttributes' => [
                'class' => 'highlevelFieldButton ',
                'data-highlevel-identifier' => $this->getIdentifier(),
                'data-highlevel-source-name' => '[' . $data['tableName'] . '][' . $data['vanillaUid'] . '][' . $this->getSourceFields()[0] . ']',
                'data-highlevel-target-name' => $data['elementBaseName'],
            ],
            'javaScriptModules' => [JavaScriptModuleInstruction::create('@mabolek/highlevel/FieldButton.js')],
        ];
    }

    /**
     * Returns the icon identifier for the button by checking if $icon is an icon file or an icon identifier.
     *
     * @param string|null $icon
     * @return string
     */
    private function getIconIdentifier(?string $icon = null): string
    {
        if ($this->iconIdentifier !== null) {
            return $this->iconIdentifier;
        }

        if ($icon === null) {
            $icon = $this->getIcon();
        }

        if (!file_exists(GeneralUtility::getFileAbsFileName($icon))) {
            return $icon;
        }

        return $this->getIdentifier();
    }
}
