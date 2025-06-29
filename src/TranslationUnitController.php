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
                $data['status']
            );
            echo json_encode([
                'status' => 'success',
                'message' => 'Translation unit created successfully.',
                'data' => [
                    'id' => $unitId,
                    'source_text' => $data['source_text'],
                    'language_id' => $data['language_id'],
                    'status' => $data['status']
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
     *
     * @return void
     */
    public function updateTranslationUnit(int $id): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        try {
            // Assuming we have a method to update translation unit
            $this->manager->updateTranslationUnit($id, $data['source_text'], $data['status']);
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
}
