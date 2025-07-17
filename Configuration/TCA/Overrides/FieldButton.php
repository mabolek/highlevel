<?php

use Mabolek\Highlevel\Instruction\FieldButton\FieldButtonInstruction;
use Mabolek\Highlevel\Instruction\InstructionProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;

(static function (): void {
    $instructionProvider = GeneralUtility::makeInstance(InstructionProvider::class);
    /** @var FieldButtonInstruction $instance */
    foreach ($instructionProvider->getInstructionsFor(FieldButtonInstruction::class) as $instance) {
        if ($instance->getTable() instanceof \Closure) {
            // If the closure returns boolean, it will return true if the table name should be used.
            if ((string)(new ReflectionFunction($instance->getTable()))->getReturnType() ?? '' === 'bool') {
                $tables = [];

                foreach (array_keys($GLOBALS['TCA']) as $table) {
                    if (($instance->getTable())($table)) {
                        $tables[] = $table;
                    }
                }
            // Otherwise, the closure should return an array of table names.
            } else {
                $tables = ($instance->getTable())($instance);
            }

            if ($tables === []) {
                continue;
            }
        } elseif (is_string($instance->getTable())) {
            $tables = [$instance->getTable()];
        } elseif (is_array($instance->getTable())) {
            $tables = $instance->getTable();
        } else {
            // TODO: Shouldn't end up here.
        }

        foreach ($tables as $table) {
            $instanceWithFixedTable = new FieldButtonInstruction(
                $instance->getIdentifier(),
                $instance->getLabel(),
                $instance->getIcon(),
                $table,
                $instance->getSourceField(),
                $instance->getTargetField(),
                $instance,
            );

            if ($instanceWithFixedTable->getTargetField() instanceof \Closure) {
                // If the closure returns boolean, it will return true if the table name should be used.
                if ((string)(new ReflectionFunction($instanceWithFixedTable->getTargetField()))->getReturnType() ?? '' === 'bool') {
                    $fields = [];

                    foreach (array_keys($GLOBALS['TCA'][$table]['columns']) as $field) {
                        if (($instanceWithFixedTable->getTargetField())($field)) {
                            $fields[] = $field;
                        }
                    }
                    // Otherwise, the closure should return an array of table names.
                } else {
                    $fields = ($instanceWithFixedTable->getTargetField())();
                }
            } elseif (is_string($instanceWithFixedTable->getTargetField())) {
                $fields = [$instanceWithFixedTable->getTargetField()];
            } elseif (is_array($instanceWithFixedTable->getTargetField())) {
                $fields = $instanceWithFixedTable->getTargetField();
            } else {
                // TODO: Shouldn't end up here.
            }

            if ($fields === []) {
                continue;
            }

            foreach ($fields as $field) {
                if (!isset($GLOBALS['TCA'][$table]['columns'][$field])) {
                    continue;
                }

                $GLOBALS['TCA'][$table]['columns'][$field]['config']['fieldControl'][
                    $instanceWithFixedTable->getIdentifier()
                ] = [
                    'renderType' => $instanceWithFixedTable->getIdentifier(),
                ];
            }
        }

        // TODO: Check caching at this point.
    }
})();
