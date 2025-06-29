<?php

namespace Roger;

require_once( getcwd() . '/TranslationStrategy.php' );

/**
 * Class TranslationFactory
 *
 * A factory class for creating appropriate translation strategy objects.
 *
 * @package Roger
 * @author gav.corbett@gmail.com
 */
class TranslationFactory
{
    /**
     * Create the appropriate translation strategy based on the target language and user.
     *
     * @param int $targetLanguage The target language for translation.
     * @param int|null $userId The user ID (optional, if omitted will be machine translation).
     * @return TranslationStrategy A concrete translation strategy.
     */
    public static function createTranslation(int $targetLanguage, int $userId = null): TranslationStrategy
    {
        // If userId is provided, use human translation
        if ($userId !== null) {
            $strategy = new HumanTranslationStrategy();
        } else {
			$strategy = new MachineTranslationStrategy();
		}

		$strategy->setLanguage($targetLanguage);
		$strategy->setUserId($userId);
		return $strategy;
    }
}
