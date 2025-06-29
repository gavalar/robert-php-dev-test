import React, { useState, useEffect } from 'react';
import axios from 'axios';
import 'font-awesome/css/font-awesome.min.css'; // Import FontAwesome

const API_URL = 'http://localhost:8000/api/translation-units'; // API endpoint for translation units
const CREATE_TRANSLATION_URL = 'http://localhost:8000/api/translation/'; // API endpoint to create translations
const UPDATE_TRANSLATION_URL = 'http://localhost:8000/api/translation/'; // API endpoint to update translations
const DELETE_TRANSLATION_UNIT_URL = 'http://localhost:8000/api/translation-units'; // API endpoint to delete translation unit
const DELETE_TRANSLATION_URL = 'http://localhost:8000/api/translation'; // API endpoint to delete a translation

function App() {
  const [translationUnits, setTranslationUnits] = useState([]);
  const [newSourceText, setNewSourceText] = useState('');
  const [languageId, setLanguageId] = useState(1); // Default to language ID 1 (for example, English)
  const [selectedUnit, setSelectedUnit] = useState(null);
  const [translatedText, setTranslatedText] = useState('');
  const [targetLanguageId, setTargetLanguageId] = useState(2); // Default target language to French
  const [error, setError] = useState(null);

  // List of languages
  const languages = [
    { id: 1, name: 'English' },
    { id: 2, name: 'French' },
    // Add more languages here as needed
  ];

  // Fetch translation units from the API
  useEffect(() => {
    fetchTranslationUnits();
  }, []);

  const fetchTranslationUnits = async () => {
    try {
      const response = await axios.get(API_URL);
      if (response.data.status === 'success') {
        setTranslationUnits(response.data.data); // Set translation units to state
      } else {
        setError('Failed to load translation units');
      }
    } catch (error) {
      console.error('Error fetching translation units:', error);
      setError('Error fetching translation units');
    }
  };

  // Handle selecting a translation unit for translation
  const handleSelectUnit = (unit) => {
    setSelectedUnit(unit);
    setTranslatedText(unit.translated_text || ''); // Pre-fill the translation if available
    setTargetLanguageId(unit.language_id === 1 ? 2 : 1); // Set default target language based on source language
  };

  // Handle adding or updating the translation
  const handleSubmitTranslation = async () => {
    if (!translatedText) {
      setError('Please provide a translated text.');
      return;
    }

    try {
      // If translation already exists, update it
      const url = selectedUnit.translated_text
        ? `${UPDATE_TRANSLATION_URL}${selectedUnit.id}` // Update translation
        : `${CREATE_TRANSLATION_URL}${selectedUnit.id}`; // Create translation

      await axios.post(url, {
        translated_text: translatedText, // The translated text
        language_id: targetLanguageId,   // The target language code
      });

      console.log("Translation submitted successfully.");
      // Reset after successful translation submission
      setSelectedUnit(null);
      setTranslatedText('');
      fetchTranslationUnits(); // Refresh the list of units
    } catch (error) {
      console.error('Error submitting translation:', error);
      setError('Error submitting translation');
    }
  };

  // Handle adding a new translation unit
  const handleAddTranslationUnit = async () => {
    if (!newSourceText) {
      setError('Please provide a source text.');
      return;
    }

    try {
      await axios.post(API_URL, {
        source_text: newSourceText,
        language_id: languageId, // Send the language ID
      });

      setNewSourceText('');
      setLanguageId(1); // Reset to default language ID
      fetchTranslationUnits(); // Refresh the list of units after adding a new one
    } catch (error) {
      console.error('Error adding translation unit:', error);
      setError('Error adding translation unit');
    }
  };

  // Handle deleting a translation unit
  const handleDeleteTranslationUnit = async (unitId) => {
    try {
      await axios.delete(`${DELETE_TRANSLATION_UNIT_URL}/${unitId}`);
      fetchTranslationUnits(); // Refresh the list after deletion
    } catch (error) {
      console.error('Error deleting translation unit:', error);
      setError('Error deleting translation unit');
    }
  };

  // Handle deleting a translation
  const handleDeleteTranslation = async (translationId) => {
    try {
      await axios.delete(`${DELETE_TRANSLATION_URL}/${translationId}`);
      fetchTranslationUnits(); // Refresh the list after deletion
    } catch (error) {
      console.error('Error deleting translation:', error);
      setError('Error deleting translation');
    }
  };

  return (
    <div className="App">
      <h1>Translation Units</h1>

      {/* Display error message */}
      {error && <div style={{ color: 'red' }}>{error}</div>}

      {/* Add Translation Unit Form */}
      <div>
        <h2>Add Translation Unit</h2>
        <input
          type="text"
          value={newSourceText}
          onChange={(e) => setNewSourceText(e.target.value)}
          placeholder="Enter source text"
        />
        <select
          value={languageId}
          onChange={(e) => setLanguageId(Number(e.target.value))}
        >
          {languages.map((lang) => (
            <option key={lang.id} value={lang.id}>
              {lang.name}
            </option>
          ))}
        </select>
        <button onClick={handleAddTranslationUnit}>Add Translation Unit</button>
      </div>

      {/* List of Translation Units */}
      <div>
        <h2>Available Translation Units</h2>
        {translationUnits.length === 0 ? (
          <p>No translation units found. You can add a new one above.</p>
        ) : (
          <ul>
            {translationUnits.map((unit) => (
              <li key={unit.id}>
                <button onClick={() => handleSelectUnit(unit)}>
                  {unit.source_text}
                </button>
                {/* Show the translation if available */}
                {unit.translated_text && (
                  <span> | Translated: {unit.translated_text}</span>
                )}
                {/* Delete Translation Unit Button */}
                <button onClick={() => handleDeleteTranslationUnit(unit.id)}>
                  <i className="fa fa-trash" style={{ color: 'red', marginLeft: '10px' }}></i>
                </button>
                {/* If translation exists, show the delete button for the translation */}
                {unit.translated_text && (
                  <button onClick={() => handleDeleteTranslation(unit.id)}>
                    <i className="fa fa-trash" style={{ color: 'red', marginLeft: '10px' }}></i>
                  </button>
                )}
              </li>
            ))}
          </ul>
        )}
      </div>

      {/* Translation Form */}
      {selectedUnit && (
        <div>
          <h2>Translate: {selectedUnit.source_text}</h2>
          {/* Language dropdown for target language */}
          <select
            value={targetLanguageId}
            onChange={(e) => setTargetLanguageId(Number(e.target.value))}
          >
            {languages
              .filter((lang) => lang.id !== selectedUnit.language_id) // Exclude source language
              .map((lang) => (
                <option key={lang.id} value={lang.id}>
                  {lang.name}
                </option>
              ))}
          </select>
          <textarea
            value={translatedText}
            onChange={(e) => setTranslatedText(e.target.value)}
            placeholder="Enter your translation"
          />
          <br />
          {/* Single button for both submit and update */}
          <button onClick={handleSubmitTranslation}>
            {selectedUnit.translated_text ? 'Update Translation' : 'Submit Translation'}
          </button>
        </div>
      )}
    </div>
  );
}

export default App;

