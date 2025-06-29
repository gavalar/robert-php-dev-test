<?php

namespace Roger;

/**
 * TranslationStrategy Interface
 *
 * Interface for handling different translation strategies (e.g., machine, human).
 */
interface TranslationStrategy
{
    public function getTranslation(int $unitId, int $targetLanguageId): string;
    public function addTranslation(int $unitId, int $targetLanguageId, string $translatedText, int $version): string;
}

/**
 * MachineTranslationStrategy (Concrete Strategy)
 *
 * Implements the TranslationStrategy interface using machine translation.
 */
class HumanTranslationStrategy implements TranslationStrategy
{
    private Database $db;

    // Constructor to initialize the Database instance
    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Retrieves the latest translation for a translation unit in a specific target language.
     *
     * @param int $unitId The translation unit ID.
     * @param int $targetLanguageId The target language ID (e.g., French).
     * @return string The translated text.
     */
    public function getTranslation(int $unitId, int $targetLanguageId): string
    {
        // Check if the translation exists in the database
        $query = "
            SELECT translated_text
            FROM Translations
            WHERE translation_unit_id = :unitId AND target_language_id = :targetLanguageId
            ORDER BY version DESC
            LIMIT 1
        ";
        $params = [':unitId' => $unitId, ':targetLanguageId' => $targetLanguageId];
        $translation = $this->db->fetch($query, $params);

        if (!is_null($translation)) {
            return $translation['translated_text'];
        }

        // Check if the translation exists in the database
        $query = "
            SELECT source_text
            FROM Translation_Units
            WHERE language_id = :targetLanguageId
            ORDER BY id DESC
            LIMIT 1
        ";
        $params = [':targetLanguageId' => $targetLanguageId];
        $source = $this->db->fetch($query, $params);

        if (!is_null($source)) {
            return $source['source_text'];
        }

        return "No Source or Translated Text found for that language";
    }

    /**
     * Adds a new translation for the translation unit.
     *
     * @param int $unitId The translation unit ID.
     * @param int $targetLanguageId The target language ID (e.g., French).
     * @param string $translatedText The translated text to add.
     * @param int $version The version of the translation.
     * @return string A message indicating the success or failure of the operation.
     * @throws Exception If an error occurs during the database transaction.
     */
    public function addTranslation(int $unitId, int $targetLanguageId, string $translatedText, int $version): string
    {
        // Begin the database transaction
        $this->db->beginTransaction();

        try {
            // Insert the translation into the Translations table
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
            $translationId = $this->db->getInstance()->lastInsertId();

            // Insert into Translation_Versions table for version tracking
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

            // Commit the transaction
            $this->db->commit();

            return "Machine translation added successfully.";
        } catch (Exception $e) {
            // Rollback the transaction in case of an error
            $this->db->rollBack();
            throw $e;
        }
    }
}
