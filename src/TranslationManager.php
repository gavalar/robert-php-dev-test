<?php
namespace Roger;

require_once(dirname(__FILE__) . '/Loader.php');

/**
 * Class TranslationManager
 *
 * Manages translation units, translations, version history, and tracks changes.
 *
 * @package Roger
 * @author gav.corbett@gmail.com
 */
class TranslationManager
{
    private static ?TranslationManager $instance = null;
    protected Database $db;
    private TranslationStrategy $strategy; // Strategy Pattern
    private array $valid_language = array(); // Caching for the Language test (prevent multiple queries to the DB)

    /**
     * TranslationManager constructor.
     *
     * Initializes the TranslationManager with a database connection.
     *
     * @param Database|null $db The Database instance (optional).
     */
    private function __construct(Database $db = null)
    {
        $this->db = $db ?: Database::getInstance();
        $this->setStrategy();
    }

    /**
     * Sets the Strategy that the translation will use
     *
     * @param int|null $strategy The strategy to handle translations (optional).
     * @return self The current instance of TranslationManager for method chaining.
     */
    public function setStrategy(int $strategy = null): self
    {
        if (!TranslationFactory::isValidStrategy($strategy)) {
            $strategy = TranslationFactory::TYPE_HUMAN;
        }

        $this->strategy = TranslationFactory::createTranslationStrategy($strategy);// Default to MachineTranslation

        return $this;
    }

    /**
     * Get the single instance of TranslationManager.
     *
     * @param Database|null $db The Database instance (optional).
     * @param TranslationStrategy|null $strategy The strategy to handle translations (optional).
     * @return TranslationManager The instance of TranslationManager.
     */
    public static function getInstance(Database $db = null, TranslationStrategy $strategy = null): TranslationManager
    {
        if (self::$instance === null) {
            self::$instance = new TranslationManager($db);
        }
        return self::$instance;
    }

    /**
     * Add a new translation unit to the database.
     *
     * @param string $sourceText The original text that needs to be translated.
     * @param int $languageId The language ID of the source language.
     * @param string $status The status of the translation unit (default: 'untranslated').
     * @return int The ID of the last insert
     * @throws Exception if there is an invalid Language ID
     */
    public function addTranslationUnit(string $sourceText, int $languageId, string $status = 'untranslated'): int
    {
        if (!$this->isValidLanguage($languageId)) {
            throw new \Exception(sprintf('Language Code %d is invalid', $languageId));
        }

        $query = "
            INSERT INTO Translation_Units (source_text, language_id, status, created_at, updated_at)
            VALUES (:source_text, :language_id, :status, NOW(), NOW())
        ";
        $params = [
            ':source_text' => $sourceText,
            ':language_id' => $languageId,
            ':status' => $status
        ];
        $this->db->execute($query, $params);
        return $this->db->getLastInsertId();
    }


    /**
     * Insert or update translation and create a new version entry.
     *
     * @param int $unitId The ID of the translation unit.
     * @param int $languageId The ID of the language.
     * @return boolean If found in the DB return true otherwise is false
     */
    public function isValidLanguage(int $languageId): bool
    {
        $this->valid_language[$languageId] = false;

        // Get the previous translation version (if available)
        $previousVersionQuery = "
            SELECT * FROM Languages
            WHERE id = :id
            ORDER BY id DESC LIMIT 1
        ";
        $language = $this->db->fetch($previousVersionQuery, [':id' => $languageId]);

        if (is_null($language)) {
            return false;
        }

        $this->valid_language[$languageId] = true;
        return true;
    }

    /**
     * Insert or update translation and create a new version entry.
     *
     * @param int $unitId The ID of the translation unit.
     * @param int $targetLanguageId The ID of the target language for the translation.
     * @param string $translatedText The translated text to be stored.
     * @return string A message indicating the result of the operation.
     * @throws Exception If an error occurs during the database transaction.
     * @throws Exception if there is an invalid Language ID
     */
    public function addTranslation(int $unitId, int $targetLanguageId, string $translatedText): string
    {
        if (!$this->isValidLanguage($targetLanguageId)) {
            throw new \Exception('Language Code is invalid');
        }

        // Begin the transaction
        $this->db->beginTransaction();
        $version = 1;

        try {
            // Get the previous translation version (if available)
            $previousVersionQuery = "
                SELECT translation_unit_id, version FROM Translations
                WHERE translation_unit_id = :translation_id
                ORDER BY version DESC LIMIT 1
            ";
            $previousVersion = $this->db->fetch($previousVersionQuery, [':translation_id' => $unitId]);
            if (!is_null($previousVersion)) {
                $version = $previousVersion['version'] + 1;
            }

            // Insert the new translation
            $query = "
                INSERT INTO Translations (translation_unit_id, target_language_id, translated_text, version, created_at, updated_at)
                VALUES (:unitId, :targetLanguageId, :translatedText, :version, NOW(), NOW())
            ";
            $params = [
                ':unitId' => $unitId,
                ':targetLanguageId' => $targetLanguageId,
                ':translatedText' => $translatedText,
                ':version' => $version
            ];
            $this->db->execute($query, $params);

            // Get the last inserted translation ID
            $translationId = $this->db->getLastInsertId();

            // Insert into Translation_Versions table
            $query = "
                INSERT INTO Translation_Versions (translation_id, version, translated_text, created_at)
                VALUES (:translationId, :version, :translatedText, NOW())
            ";
            $params = [
                ':translationId' => $translationId,
                ':version' => $version,
                ':translatedText' => $translatedText
            ];
            $this->db->execute($query, $params);

            $query = "
                UPDATE Translation_Units SET status = :status WHERE id = :unitId
            ";
            $params = [
                ':unitId' => $unitId,
                ':status' => 'in-progress'
            ];
            $this->db->execute($query, $params);
            // Commit the transaction
            $this->db->commit();

            return "Translation and version added successfully.";
        } catch (Exception $e) {
            // If any error occurs, roll back the transaction
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Retrieve the latest translation (or the original if not translated).
     *
     * @param int $unitId The ID of the translation unit.
     * @param int $targetLanguageId The ID of the target language.
     * @return array The translated text or the original source text if no translation exists.
     * @throws Exception if there is an invalid Language ID
     */
    public function getTranslation(int $unitId, int $targetLanguageId): array
    {
        if (!$this->isValidLanguage($targetLanguageId)) {
            throw new \Exception(sprintf('Language Code %d is invalid', $targetLanguageId));
        }

        return [
            'translated' => $this->strategy->getTranslation($unitId, $targetLanguageId),
            'translatedLanguageId' => $targetLanguageId
        ];
    }
}
