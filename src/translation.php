<?php
namespace Roger;

require_once(dirname(__FILE__) . '/Loader.php');

try {
// Initialize the TranslationUnit
$manager = TranslationManager::getInstance();

// Add a translation unit (English to be translated to French)
$sourceText = "Hello, world!";
$languageId = 1; // Assuming 1 is for English in the languages table
$status = 'untranslated'; // Initial status

$unitId = $manager->addTranslationUnit($sourceText, $languageId);

// Add a Translation (French Translation for User 1)
$translatedText = "Bonjour le monde! v1";
$targetLanguageId = 2; // Assuming 2 is for French in the languages table
$manager->addTranslation($unitId, $targetLanguageId, $translatedText);

$translatedText = "Bonjour le monde! v2";
$manager->addTranslation($unitId, $targetLanguageId, $translatedText);

// Retrieve the Latest Translation (or Original)
$translation = $manager->getTranslation($unitId, $targetLanguageId);

echo "Translation: " . print_r($translation, true) . PHP_EOL;
} catch (\Exception $e) {
    echo "An error occurred: " . $e->getMessage() . PHP_EOL;
}
