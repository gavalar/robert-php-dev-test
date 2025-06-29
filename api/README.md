# Useful API commands

## View all Units
```
curl -X GET http://localhost:8000/api/translation-units
```

## View a single Unit
```
curl -X GET http://localhost:8000/api/translation-units/{INSERT NUMBER HERE}
```

## Unit CRUD
### Create a Unit
```
 curl -X POST http://localhost:8000/api/translation-units \
    -H "Content-Type: application/json" \
    --data '{
        "source_text": "Hello, API!",
        "language_id": 1
    }'
```

### Update a unit
```
curl -X PUT http://localhost:8000/api/translation-units/{INSERT NUMBER HERE} \
    -H "Content-Type: application/json" \
    --data '{
        "source_text": "Hello, My Friend!",
        "language_id": 1
    }'
```

### DELETE a unit
```
curl -X DELETE http://localhost:8000/api/translation-units/{INSERT NUMBER HERE}
```
## Translation CRUD
### Create a translation
```
 curl -X POST http://localhost:8000/api/translation/{INSERT NUMBER HERE} \
    -H "Content-Type: application/json" \
    --data '{
        "translated_text": "Bonjor, API!",
        "language_id": 2
    }'
```

### Update a translation
```
curl -X PUT http://localhost:8000/api/translation/{INSERT NUMBER HERE} \
    -H "Content-Type: application/json" \
    --data '{
        "translated_text": "Bonjour, mon ami!",
        "language_id": 2
    }'
```

### DELETE a translation
```
 curl -X DELETE http://localhost:8000/api/translation/{INSERT NUMBER HERE}
```
