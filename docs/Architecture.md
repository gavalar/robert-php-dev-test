# Roger Computer-Assisted Translation

## Design Patterns and Architecture

### Structure the system to handle multilingual content

* **Translation Units:** Organise content into translation units (e.g., phrases, sentences, paragraphs). Each translation unit should be a separate entity for easy management and update.
* **Language Handling:** Use language codes (e.g., `en` for English, `fr` for French) to categorise and manage the different language pairs.
* **Translation Workflow:** A pipeline where the source content is processed into translation units, translated, and stored, with versioning for each translated unit.
* **Multilingual Database:** The system needs to be designed for multilingual support at scale, allowing the addition of more languages easily.

### Design patterns

* **Factory Pattern:** UTo create translation units dynamically based on the source content. This generates translation units that can vary depending on the source document’s structure.
* **Strategy Pattern:** For handling different translation strategies (e.g., machine translation vs human translation). This pattern will allow flexibility in switching between strategies at runtime.
* **Observer Pattern:** Would be useful for handling changes in translation units. For example, whenever a translation unit is modified, all interested parties (e.g., editors, review teams) would be notified of changes.
* **Singleton Pattern:** Can be used for handling configuration settings or managing the central translation memory system to ensure only one instance of these settings or the translation memory exists.
* **Decorator Pattern:** This pattern could be applied to enhance the translation unit with additional metadata (e.g., revision history, translation status) without modifying the core translation unit class.

### Database schema

I had two ideas for the DB schema one a four table version and one a two table version however I have decided to use the four table version because the four-table schema is better due to its flexibility, scalability, and maintainability. It allows for better data integrity, version control, and the ability to expand with new features and languages.  Where as the two column one is mainly for simplicity, faster implementation, and lower overhead.

**languages**: Stores language information.

* `id` (INT, Primary Key)
* `code` (VARCHAR, unique, e.g., 'en', 'fr')
* `name` (VARCHAR, e.g., 'English', 'French')

**translation_units**: Stores translation units, i.e., the segments that need to be translated.

* `id` (INT, Primary Key)
* `source_text` (TEXT)
* `language_id` (INT, Foreign Key to `languages`)
* `status` (VARCHAR, e.g., 'untranslated', 'in-progress', 'completed')

**translations**: Stores translated text for each translation unit.

* `id` (INT, Primary Key)
* `translation_unit_id` (INT, Foreign Key to `translation_units`)
* `target_language_id` (INT, Foreign Key to `languages`)
* `translated_text` (TEXT)
* `version` (INT)
* `created_at` (TIMESTAMP)
* `updated_at` (TIMESTAMP)

**translation_versions**: To store different versions of translations for historical tracking.

* `id` (INT, Primary Key)
* `translation_id` (INT, Foreign Key to `translations`)
* `version` (INT)
* `translation_text` (TEXT)
* `created_at` (TIMESTAMP)

### Version control for translations

To implement version control for translations, each translation can be stored in a versioned table (`translation_versions`), ensuring that every update to a translation unit is tracked. A translation unit may go through multiple revisions, and each revision should have its own version number, so you can:

* **Track Changes**: Each translation revision is saved in a versioned table, providing a historical record of changes.
* **Rollback**: If needed, you can revert to an earlier version of a translation.
* **Merge Conflicts**: In cases where multiple translators are working on the same unit, conflicts can be flagged, and a merge process can be introduced.

Version control can be integrated with the Observer Pattern, notifying translators and project managers about the availability of new versions or changes.

