<?php
namespace Roger;
require_once(dirname(__FILE__) . '/../src/Loader.php');

use PHPUnit\Framework\TestCase;
use Roger\TranslationManager;
use Roger\Database;

class TranslationManagerTest extends TestCase
{
    private TranslationManager $manager;

    /**
     * Set up a new TranslationManager instance for testing.
     *
     * This method runs before each test and initializes the TranslationManager instance.
     */
    protected function setUp(): void
    {
        $this->manager = TranslationManager::getInstance();
    }

    /**
     * Test adding a new translation unit.
     *
     * This test checks whether the addTranslationUnit method adds a new unit to the database
     * and returns a valid unit ID.
     */
    public function testAddTranslationUnit()
    {
        $sourceText = "Hello, world!";
        $languageId = 1; // Assuming language_id 1 is valid (e.g., English)
        $status = 'untranslated';

        $unitId = $this->manager->addTranslationUnit($sourceText, $languageId, $status);

        $this->assertIsInt($unitId);
    }

    /**
     * Test adding a translation.
     *
     * This test ensures that the addTranslation method adds a new translation successfully
     * and returns a success message.
     */
    public function testAddTranslation()
    {
        // Mock translation unit ID and target language
        $unitId = 1;
        $targetLanguageId = 2; // Assuming 2 is French
        $translatedText = "Bonjour le monde!";

        $message = $this->manager->addTranslation($unitId, $targetLanguageId, $translatedText);
        $this->assertEquals("Translation and version added successfully.", $message);
    }

    /**
     * Test retrieving a translation.
     *
     * This test checks if the getTranslation method correctly retrieves the latest translation
     * based on unit ID and target language ID, and returns the expected translated text.
     */
    public function testGetTranslation()
    {
        $unitId = 1;
        $targetLanguageId = 2;

        $translation = $this->manager->getTranslation($unitId, $targetLanguageId);
        $this->assertArrayHasKey('translated', $translation);
        $this->assertEquals("Bonjour le monde!", $translation['translated']);
    }
}

