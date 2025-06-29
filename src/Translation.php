<?php
namespace Roger;

require_once( getcwd() . '/TranslationManager.php' );

try {
    // Add a translation unit in the source language
    $manager = TranslationManager::getInstance();
//    $manager->addTranslationUnit("Hello, world!", TranslationManager::LANG_ENGLISH);
//
//  // User A (human) translates to French
//    $translatorA = TranslationFactory::createTranslation(TranslationManager::LANG_FRENCH, 1); // User 1 (human) translating to French
//    echo $translatorA->setTranslation("Hello, world!", TranslationManager::LANG_ENGLISH)
//                     ->translate("Bonjour le monde!") . PHP_EOL;
//
//    // User B (machine) translates to French (no user ID)
//    $translatorB = TranslationFactory::createTranslation(TranslationManager::LANG_FRENCH); // No user ID (machine translation)
//    echo $translatorB->setTranslation("Hello, world!", TranslationManager::LANG_ENGLISH, TranslationStrategy::TYPE_TRANSLATED)
//                     ->translate("Salut tout le monde!") . PHP_EOL;

	$unit = $manager->getTranslationUnit("Hello, world!", TranslationManager::LANG_ENGLISH);
	echo $manager->getTranslationByIdAndLanguage($unit['id'], TranslationManager::LANG_ENGLISH) . PHP_EOL;
	echo $manager->getTranslationByIdAndLanguage($unit['id'], TranslationManager::LANG_FRENCH) . PHP_EOL;

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
