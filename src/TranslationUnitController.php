<?php
namespace Roger;

require_once(dirname(__FILE__) . '/TranslationManager.php');

use Roger\TranslationManager;
use Exception;

/**
 * TranslationUnitController
 *
 * Controller responsible for handling translation unit-related API requests.
 * Provides methods for CRUD operations on translation units.
 *
 * @package Roger\Controller
 */
class TranslationUnitController
{
    /**
     * @var TranslationManager
     * @readonly
     */
    protected TranslationManager $manager;

    /**
     * TranslationUnitController constructor.
     *
     * Initializes the TranslationManager instance to interact with translation units.
     * This constructor will ensure that the TranslationManager instance is available
     * for all the methods within the controller.
     */
    public function __construct()
    {
        // Initialize the TranslationManager instance
        $this->manager = TranslationManager::getInstance();
    }

    /**
     * List all translation units.
     *
     * This method fetches and returns all translation units from the database.
     * It will return a JSON response with the translation units.
     *
     * @return void
     */
    public function listTranslationUnits(): void
    {
        try {
            $units = $this->manager->getTranslationUnits();
            echo json_encode([
                'status' => 'success',
                'data' => $units
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get a specific translation unit by ID.
     *
     * This method retrieves a specific translation unit by its ID.
     * If the unit is found, it returns a JSON response with the unit's details.
     *
     * @param int $id The ID of the translation unit to retrieve.
     *
     * @return void
     */
    public function getTranslationUnit(int $id): void
    {
        try {
            $unit = $this->manager->getTranslationUnitById($id);  // Assuming method for fetching unit by ID
            if ($unit) {
                echo json_encode([
                    'status' => 'success',
                    'data' => $unit
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Translation unit not found'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Create a new translation unit.
     *
     * This method accepts JSON data to create a new translation unit in the database.
     * The request body should contain the source text, language ID, and the status of the unit.
     *
     * @return void
     */
    public function createTranslationUnit(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        try {
            // Assuming data validation is done here
            $unitId = $this->manager->addTranslationUnit(
                $data['source_text'],
                $data['language_id'],
            );
            echo json_encode([
                'status' => 'success',
                'message' => 'Translation unit created successfully.',
                'data' => [
                    'id' => $unitId,
                    'source_text' => $data['source_text'],
                    'language_id' => $data['language_id'],
                ]
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update an existing translation unit.
     *
     * This method updates the source text and/or status of a translation unit
     * identified by the provided ID.
     *
     * @param int $id The ID of the translation unit to update.
     * @return void
     */
    public function updateTranslationUnit(int $id): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        try {
            // Assuming we have a method to update translation unit
            $this->manager->updateTranslationUnit($id, $data['source_text']);
            echo json_encode([
                'status' => 'success',
                'message' => 'Translation unit updated successfully.'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Delete a translation unit by ID.
     *
     * This method deletes a translation unit from the database based on the given ID.
     *
     * @param int $id The ID of the translation unit to delete.
     *
     * @return void
     */
    public function deleteTranslationUnit(int $id): void
    {
        try {
            $this->manager->deleteTranslationUnit($id);  // Assuming method to delete unit
            echo json_encode([
                'status' => 'success',
                'message' => 'Translation unit deleted successfully.'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Create a new translation.
     *
     * This method accepts JSON data to create a new translation for a specific translation unit.
     * The request body should contain the unit_id, target_language_id, and translated_text.
     *
     * @param int $id The ID of the translation unit to translate.
     * @return void
     */
    public function createTranslation(int $id): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        try {
            // Check if required fields are present
            if (empty($id) || empty($data['language_id']) || empty($data['translated_text'])) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Missing required parameters: unit_id, target_language_id, translated_text.'
                ]);
                return;
            }

            // Create the translation
            $message = $this->manager->addTranslation(
                $id,
                $data['language_id'],
                $data['translated_text']
            );

            echo json_encode([
                'status' => 'success',
                'message' => $message,
                'data' => [
                    'unit_id' => $id,
                    'target_language_id' => $data['language_id'],
                    'translated_text' => $data['translated_text']
                ]
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update an existing translation.
     *
     * This method updates the translated text for a translation identified by the unit ID and target language ID.
     *
     * @param int $id The ID of the translation unit to update.
     * @return void
     */
    public function updateTranslation(int $id): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        try {
            // Validate the input
            if (empty($id) || empty($data['language_id']) || empty($data['translated_text'])) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Missing required parameters: unit_id, target_language_id, translated_text.'
                ]);
                return;
            }

            // Update the translation
            $this->manager->updateTranslation($id, $data['language_id'], $data['translated_text']);
            echo json_encode([
                'status' => 'success',
                'message' => 'Translation updated successfully.'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Delete a translation by ID.
     *
     * This method deletes a specific translation from the database based on the provided translation ID.
     *
     * @param int $id The ID of the translation to delete.
     * @return void
     */
    public function deleteTranslation(int $id): void
    {
        try {
            $this->manager->deleteTranslation($id);  // Assuming method to delete translation
            echo json_encode([
                'status' => 'success',
                'message' => 'Translation deleted successfully.'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
