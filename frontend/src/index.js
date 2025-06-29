import React, { useState, useEffect } from "react";
import axios from "axios";

// Base URL for the API
const API_URL = "http://localhost/api/translation-units";

const TranslationManager = () => {
    const [translationUnits, setTranslationUnits] = useState([]);
    const [newTranslation, setNewTranslation] = useState({
        source_text: "",
        language_id: 1,  // Default language ID, assuming '1' is for English
        status: "untranslated",
    });
    const [editedTranslation, setEditedTranslation] = useState({
        id: null,
        source_text: "",
        status: "",
    });

    // Fetch translation units on component mount
    useEffect(() => {
        const fetchTranslationUnits = async () => {
            try {
                const response = await axios.get(API_URL);
                setTranslationUnits(response.data.data); // Assuming API returns a `data` field with translation units
            } catch (error) {
                console.error("Error fetching translation units:", error);
            }
        };

        fetchTranslationUnits();
    }, []);

    // Add a new translation unit
    const handleAddTranslation = async () => {
        try {
            const response = await axios.post(API_URL, newTranslation);
            setTranslationUnits([...translationUnits, response.data.data]);
            setNewTranslation({
                source_text: "",
                language_id: 1,
                status: "untranslated",
            });
        } catch (error) {
            console.error("Error adding translation unit:", error);
        }
    };

    // Handle editing a translation unit
    const handleEditTranslation = async () => {
        if (!editedTranslation.id) return;

        try {
            await axios.put(`${API_URL}/${editedTranslation.id}`, {
                source_text: editedTranslation.source_text,
                status: editedTranslation.status,
            });

            const updatedUnits = translationUnits.map((unit) =>
                unit.id === editedTranslation.id
                    ? { ...unit, ...editedTranslation }
                    : unit
            );
            setTranslationUnits(updatedUnits);
            setEditedTranslation({ id: null, source_text: "", status: "" });
        } catch (error) {
            console.error("Error editing translation unit:", error);
        }
    };

    return (
        <div>
            <h1>Translation Manager</h1>
            <div>
                <h2>Add New Translation</h2>
                <input
                    type="text"
                    placeholder="Source Text"
                    value={newTranslation.source_text}
                    onChange={(e) =>
                        setNewTranslation({ ...newTranslation, source_text: e.target.value })
                    }
                />
                <select
                    value={newTranslation.language_id}
                    onChange={(e) =>
                        setNewTranslation({ ...newTranslation, language_id: e.target.value })
                    }
                >
                    <option value="1">English</option>
                    <option value="2">French</option>
                    {/* Add other languages here */}
                </select>
                <button onClick={handleAddTranslation}>Add Translation</button>
            </div>

            <h2>Translation Units</h2>
            <ul>
                {translationUnits.slice(0, 10).map((unit) => (
                    <li key={unit.id}>
                        <p>{unit.source_text}</p>
                        <p>Status: {unit.status}</p>
                        <button onClick={() => setEditedTranslation({ id: unit.id, source_text: unit.source_text, status: unit.status })}>
                            Edit
                        </button>
                    </li>
                ))}
            </ul>

            {editedTranslation.id && (
                <div>
                    <h2>Edit Translation Unit</h2>
                    <input
                        type="text"
                        value={editedTranslation.source_text}
                        onChange={(e) =>
                            setEditedTranslation({ ...editedTranslation, source_text: e.target.value })
                        }
                    />
                    <input
                        type="text"
                        value={editedTranslation.status}
                        onChange={(e) =>
                            setEditedTranslation({ ...editedTranslation, status: e.target.value })
                        }
                    />
                    <button onClick={handleEditTranslation}>Update Translation</button>
                </div>
            )}
        </div>
    );
};

export default TranslationManager;
