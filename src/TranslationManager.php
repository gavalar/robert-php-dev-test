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

    /**
     * Retrieve a list of translation units with a limit on the number of units.
     *
     * @param int $limit The maximum number of translation units to return (defaults to 10).
     * @return array The list of translation units.
     * @throws Exception If there is an error with the database query.
     */
    public function getTranslationUnits(int $limit = 10): array
    {
        try {
            $query = sprintf("SELECT * FROM Translation_Units LIMIT %d", $limit);
            $params = [];
            return $this->db->fetchAll($query, $params);
        } catch (Exception $e) {
            throw new Exception("Failed to fetch translation units: " . $e->getMessage());
        }
    }

    /**
     * Retrieve a translation unit by its ID.
     *
     * @param int $id The ID of the translation unit.
     * @return array The translation unit data.
     * @throws Exception If the translation unit is not found.
     */
    public function getTranslationUnitById(int $id): array
    {
        try {
            $query = "SELECT * FROM Translation_Units WHERE id = :id LIMIT 1";
            $params = [':id' => $id];
            $result = $this->db->fetch($query, $params);

            if (!$result) {
                throw new Exception("Translation unit with ID $id not found.");
            }

            return $result;
        } catch (Exception $e) {
            throw new Exception("Error retrieving translation unit: " . $e->getMessage());
        }
    }

    /**
     * Update an existing translation unit.
     *
     * This method updates the source text and/or status of a translation unit identified by its ID.
     *
     * @param int $unitId The ID of the translation unit to update.
     * @param string|null $sourceText The new source text to set (optional).
     * @param string|null $status The new status to set (optional).
     * @return void
     * @throws Exception If an error occurs during the update.
     */
    public function updateTranslationUnit(int $unitId, ?string $sourceText = null, ?string $status = null): void
    {
        // Start building the query and parameters
        $query = "UPDATE Translation_Units SET updated_at = NOW()";
        $params = [':unitId' => $unitId];

        // Add source_text update if provided
        if ($sourceText !== null) {
            $query .= ", source_text = :sourceText";
            $params[':sourceText'] = $sourceText;
        }

        // Add status update if provided
        if ($status !== null) {
            $query .= ", status = :status";
            $params[':status'] = $status;
        }

        // Finish the query with the WHERE clause
        $query .= " WHERE id = :unitId";

        try {
            $this->db->execute($query, $params);  // Execute the query
        } catch (Exception $e) {
            throw new Exception("Error updating translation unit: " . $e->getMessage());
        }
    }

    /**
     * Delete a translation unit by ID.
     *
     * This method deletes a translation unit from the database based on the given ID.
     *
     * @param int $unitId The ID of the translation unit to delete.
     * @return void
     * @throws Exception If an error occurs during deletion.
     */
    public function deleteTranslationUnit(int $unitId): void
    {
        try {
            $query = "DELETE FROM Translation_Units WHERE id = :unitId";
            $params = [':unitId' => $unitId];
            $this->db->execute($query, $params);  // Execute the query
        } catch (Exception $e) {
            throw new Exception("Error deleting translation unit: " . $e->getMessage());
        }
    }

    /**
     * Update an existing translation by translation ID and language ID.
     *
     * This method updates the translated text for a translation identified by the translation ID and language ID.
     *
     * @param int $translationId The ID of the translation to update.
     * @param int $languageId The ID of the target language for the translation.
     * @param string $translatedText The new translated text to replace the old one.
     * @return void
     * @throws Exception If an error occurs during the update.
     */
    public function updateTranslation(int $translationId, int $languageId, string $translatedText): void
    {
        if (!$this->isValidLanguage($languageId)) {
            throw new \Exception(sprintf('Language Code %d is invalid', $languageId));
        }

        // First, validate that the translation exists for the provided language ID
        $query = "
            SELECT * FROM Translations
            WHERE id = :translationId AND target_language_id = :languageId
            LIMIT 1
        ";
        $params = [
            ':translationId' => $translationId,
            ':languageId' => $languageId
        ];

        $translation = $this->db->fetch($query, $params);

        if (!$translation) {
            throw new Exception("Translation not found for translation ID $translationId and language ID $languageId.");
        }

        // Update the translation with the new translated text
        $query = "
            UPDATE Translations
            SET translated_text = :translatedText, updated_at = NOW()
            WHERE id = :translationId AND target_language_id = :languageId
        ";
        $params = [
            ':translatedText' => $translatedText,
            ':translationId' => $translationId,
            ':languageId' => $languageId
        ];

        try {
            $this->db->execute($query, $params);  // Execute the query to update the translation
        } catch (Exception $e) {
            throw new Exception("Error updating translation: " . $e->getMessage());
        }
    }

    /**
     * Delete a translation by translation ID and language ID.
     *
     * This method deletes a translation from the database based on the given translation ID and language ID.
     *
     * @param int $translationId The ID of the translation to delete.
     * @return void
     * @throws Exception If an error occurs during the deletion.
     */
    public function deleteTranslation(int $translationId): void
    {
        // Validate that the translation exists in the specified language
        $query = "
            SELECT * FROM Translations
            WHERE id = :translationId
            LIMIT 1
        ";
        $params = [
            ':translationId' => $translationId
        ];

        $translation = $this->db->fetch($query, $params);

        if (!$translation) {
            throw new Exception("Translation not found for translation ID $translationId and language ID $languageId.");
        }

        // Delete the translation
        $query = "
            DELETE FROM Translations
            WHERE id = :translationId
        ";
        $params = [
            ':translationId' => $translationId
        ];

        try {
            $this->db->execute($query, $params);  // Execute the query to delete the translation
        } catch (Exception $e) {
            throw new Exception("Error deleting translation: " . $e->getMessage());
        }
    }
}
