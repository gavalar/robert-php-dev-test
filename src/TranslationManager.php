<?php

namespace Roger;
use \PDO as PDO;
use \Exception as Exception;

require_once( getcwd() . '/Database.php' );
require_once( getcwd() . '/TranslationFactory.php' );

/**
 * Class TranslationManager
 *
 * This class manages translation units, supports adding new translation units,
 * updating translations, and notifying observers of changes.
 *
 * @package Roger
 * @author gav.corbett@gmail.com
 */
class TranslationManager
{
	const LANG_ENGLISH = 1;
	const LANG_FRENCH = 2;
	const LANG_SPANISH = 3;

    private static ?TranslationManager $instance = null;
    protected Database $_db;
	protected array $_valid_languages = array(self::LANG_ENGLISH, self::LANG_FRENCH);

    /**
     * TranslationManager constructor.
     *
     * Private constructor to ensure only one instance of TranslationManager is created.
     *
     * @param \PDO|null $db The \PDO database connection (optional).
     */
    private function __construct(\PDO $db = null)
    {
        // Use default database connection if none is provided
        if ($db === null) {
			$db = Database::getInstance();
        }

        $this->_db = $db;
    }

    /**
     * Get the single instance of TranslationManager.
     *
     * @param \PDO|null $db The \PDO database connection (optional).
     * @return TranslationManager The instance of TranslationManager.
     */
    public static function getInstance(\PDO $db = null): TranslationManager
    {
        if (self::$instance === null) {
            self::$instance = new TranslationManager($db);
        }
        return self::$instance;
    }

    /**
     * Add a new translation unit to the database.
     *
     * @param string $sourceText The text to be translated.
     * @param string $sourceLanguage The source language of the text.
	 * @throws Exception
     * @return self The current instance of TranslationManager for method chaining.
     */
    public function addTranslationUnit(string $sourceText, int $sourceLanguage = self::LANG_ENGLISH): self
    {
		if (!self::isValidLanguage($sourceLanguage))
		{
			throw new \Exception('Unable to set language');
		}

        $query = "INSERT INTO Translation_Units (source_text, source_language, created_at, updated_at)
                  VALUES (:source_text, :source_language, NOW(), NOW())";
        $params = array(
            ':source_text' => $sourceText,
            ':source_language' => $sourceLanguage
        );

		$this->_db->execute($query, $params);
        return $this; // Return $this to support fluent chaining
    }

    /**
     * Retrieve a translation unit by either the original text or translated text.
     *
     * @param string $text The original or translated text to search for.
     * @param string|null $language The target language to match.
	 * @throws Exception
     * @return array|null The translation unit data, or null if not found.
     */
    public function getTranslationUnit(string $text, int $language = self::LANG_ENGLISH): ?array
    {
		if (!$this->isValidLanguage($language))
		{
			throw new \Exception('Unable to set language');
		}

        // Search for a translation unit by original text or translated text
        $query = "
            SELECT * FROM Translation_Units
            WHERE source_text = :text
            AND source_language = :language
			ORDER BY id DESC
			LIMIT 1
        ";
        $params = [
            ':text' => $text,
            ':language' => $language
        ];

        $unit = $this->_db->fetch($query, $params);
		if (!$unit) {
			throw new \Exception('Unable to find unit in the Database');
		}

		return $unit;
    }

    /**
     * Retrieve either the original or the latest translated version of a translation unit.
     *
     * @param int $translationId The ID of the translation unit.
     * @param int $language The target language.
	 * @throws Exception
     * @return string The original text or the latest translation.
     */
	public function getTranslationByIdAndLanguage(int $translationId, int $language) : string
	{
        // Fetch the translation unit by its ID
        $query = "SELECT * FROM Translation_Units WHERE id = :translationId";
        $unit = $this->_db->fetch($query, [':translationId' => $translationId]);

        if (!$unit) {
            throw new \Exception("Translation unit not found.");
        }

        // If the language is the source language, return the original text
        if ($language == $unit['source_language']) {
            return $unit['source_text'];
        }

        // Otherwise, fetch the latest translation in the target language
        $query = "
            SELECT translated_text
            FROM Translations
            WHERE translation_unit_id = :translationId
            ORDER BY version DESC LIMIT 1
        ";
        $params = [':translationId' => $translationId];
        $translation = $this->_db->fetch($query, $params);

        return $translation ? $translation['translated_text'] : "Waiting for this translation.";
    }

	/**
	 * Checks if a language is valid
	 *
	 * @param int $language
	 * @return boolean
	 */
	public function isValidLanguage(int $language): bool
	{
		if (!in_array($language, $this->_valid_languages)) {
			return false;
		}

		return true;
	}
}
