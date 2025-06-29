<?php
namespace Roger;

require_once(dirname(__FILE__) . '/Loader.php');

use \Exception as Exception;

/**
 * TranslationFactory (Factory Pattern)
 *
 * This factory creates the correct translation strategy based on the input parameters.
 */
class TranslationFactory
{
    const TYPE_HUMAN = 1;
    const TYPE_MACHINE = 2;

    /**
     * Checks for a Translation Strategy.
     *
     * @param int $type The type of translation strategy (e.g., 'machine', 'human').
     * @return Boolean If the value is valid or not
     */
    public static function isValidStrategy(int $type = null): bool
    {
        if (is_null($type) || $type !== TYPE_HUMAN || $type !== TYPE_MACHINE) {
            return false;
        }
        return true;
    }

    /**
     * Create a Translation Strategy.
     *
     * @param int $type The type of translation strategy (e.g., 'machine', 'human').
     * @return TranslationStrategy The appropriate translation strategy.
     */
    public static function createTranslationStrategy(int $type): TranslationStrategy
    {
        $db = Database::getInstance();

        switch (strtolower($type)) {
            case self::TYPE_HUMAN:
                return new HumanTranslationStrategy($db);
            case self::TYPE_MACHINE:
            default:
                throw new \Exception("Invalid translation strategy type: $type");
        }
    }
}


