<?php
namespace Roger;

/**
 * abstract class TranslationStrategy
 *
 * Shared methods that need to be used across all Strategy's.
 * Defines the methods that all translation strategies should implement.
 *
 * @package Roger
 * @author gav.corbett@gmail.com
 */
abstract class TranslationStrategy
{
	const TYPE_NEW = 1;
	const TYPE_TRANSLATED = 2;

    protected Database $_db;
	protected int $_language = 0;
	protected int $_user_id = 0;
	protected $_translation = null;

    /**
     * __construct
     *
     * @param Database|null $db Database connection.
     * @return void
     */
    public function __construct(Database $db = null)
    {
		if (is_null($db)) {
			$db = Database::getInstance();
		}
        $this->_db = $db;
    }

	/**
	 * Set's the Language for the translation.
	 *
	 * @param int $targetLanguage Language ID for the translation.
	 * @return this Fluent API.
	 */
	public function setLanguage(int $targetLanguage): self
	{
		$manager = TranslationManager::getInstance();
		if (!$manager->isValidLanguage($targetLanguage))
		{
			throw new \Exception('Unable to set language');
		}

		$this->_language = $targetLanguage;
		return $this;
	}

	/**
	 * Set's the User ID (if applicable) for the translation.
	 *
	 * @param int|null $userId The userID who set the translation.
	 * @return this Fluent API.
	 */
	public function setUserId(int $userId = null): self
	{
		if (is_int($userId)) {
			$this->_user_id = $userId;
		}
		return $this;
	}

	public function getDB()
	{
		return $this->_db;
	}

    /**
     * Retrieve a translation by its original text and source language.
     *
     * @param string $text The original text to translate.
     * @param int $sourceLanguage The source language of the text.
     * @return self Fluent API
     */
    abstract public function setTranslation(string $text, int $sourceLanguage): self;

    /**
     * Translate the given text into the target language and store it.
     *
     * @param string $translatedText The translated text.
     * @return string The translated text with versioning.
     */
    abstract public function translate(string $translatedText): string;
}

/**
 * Class HumanTranslationStrategy
 *
 * A concrete implementation of the TranslationStrategy interface using human translation.
 *
 * @package Roger
 * @author gav.corbett@gmail.com
 */
class HumanTranslationStrategy extends TranslationStrategy
{
    /**
     * Retrieve a translation by its original text and source language.
     *
     * @param string $text The original text to translate.
     * @param int $sourceLanguage The source language of the text.
	 * @throws xception
     * @return self Fluent API
     */
    public function setTranslation(string $text, int $sourceLanguage, int $type = TranslationStrategy::TYPE_NEW): self
    {
		$query = "SELECT *
				FROM Translation_Units
				WHERE source_text = :text AND source_language = :sourceLanguage
				ORDER BY id DESC
	   			LIMIT 1";
		$params = array(':text' => $text, ':sourceLanguage' => $sourceLanguage);

		if ($type === TranslationStrategy::TYPE_NEW) {
			$query = "SELECT *
				FROM Translation_Units
				WHERE source_text = :text AND source_language = :sourceLanguage AND translation_status = :status
				ORDER BY id DESC
				LIMIT 1";
			$params[':status'] = Database::STATUS_UNTRANSLATED;
		}

        $translation = $this->_db->fetch($query, $params);
		$this->_translation = $translation;
        if (!$translation) {
			throw new \Exception("No translation found.");
		}

		return $this;
    }

    /**
     * Translate the given text into the target language and store it.
     *
     * @param string $translatedText The translated text.
     * @return string The translated text with versioning.
     */
    public function translate(string $translatedText): string
    {
        if (is_null($this->_translation)) {
			throw new \Exception("No Unit to translate");
        }

		$query = "
			SELECT IFNULL(MAX(version), 0) + 1 AS next_version
			FROM Translations
			WHERE translation_unit_id = :unitId;
		";
		$params = [
			':unitId' => $this->_translation['id']
		];

		$version = $this->_db->fetch($query, $params);

        // Insert new translation with versioning
        $query = "
            INSERT INTO Translations (translation_unit_id, user_id, translated_text, version, created_at)
            VALUES (:unitId, :userId, :translatedText, :nextVersion, NOW())
        ";
        $params = [
            ':unitId' => $this->_translation['id'],
            ':userId' => $this->_user_id,
			':nextVersion' => $version['next_version'],
            ':translatedText' => $translatedText
        ];
		$this->_db->execute($query, $params);

		// Update the status of the original text
		$query = "UPDATE Translation_Units SET translation_status = :status WHERE id = :id";
		$params = [
			':status' => Database::STATUS_IN_PROGRESS,
			':id' => $this->_translation['id']
		];
		$this->_db->execute($query, $params);

        // Return the translated text
        return $translatedText;
    }
}

/**
 * Class MachineTranslationStrategy
 *
 * A concrete implementation of the TranslationStrategyInterface using machine translation.
 *
 * @package Roger
 * @author gav.corbett@gmail.com
 */
class MachineTranslationStrategy extends TranslationStrategy
{

    /**
     * Retrieve a translation by its original text and source language.
     *
     * @param string $text The original text to translate.
     * @param int $sourceLanguage The source language of the text.
     * @return self Fluent API
     */
    public function setTranslation(string $text, int $sourceLanguage): self
    {
		return $this;
    }

    /**
     * Translate the given text into the target language and store it.
     *
     * @param string $translatedText The translated text.
     * @return string The translated text with versioning.
     */
    public function translate(string $translatedText): string
    {
		return sprintf("Unable to translate %s", $translatedText);
	}
}

