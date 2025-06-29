<?php
namespace Roger;

require_once(dirname(__FILE__) . '/../src/TranslationUnitController.php');

use Roger\TranslationUnitController as Controller;


$controller = new Controller();
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

if ($uri == '/api/translation-units' && $method == 'GET') {
    $controller->listTranslationUnits();
} elseif (preg_match('/^\/api\/translation-units\/(\d+)$/', $uri, $matches) && $method == 'GET') {
    $controller->getTranslationUnit($matches[1]);
} elseif ($uri == '/api/translation-units' && $method == 'POST') {
    $controller->createTranslationUnit();
} elseif (preg_match('/^\/api\/translation-units\/(\d+)$/', $uri, $matches) && $method == 'PUT') {
    $controller->updateTranslationUnit($matches[1]);
} elseif (preg_match('/^\/api\/translation-units\/(\d+)$/', $uri, $matches) && $method == 'DELETE') {
    $controller->deleteTranslationUnit($matches[1]);
} elseif (preg_match('/^\/api\/translation\/(\d+)$/', $uri, $matches) && $method == 'POST') {
    $controller->createTranslation($matches[1]);
} elseif (preg_match('/^\/api\/translation\/(\d+)$/', $uri, $matches) && $method == 'PUT') {
    $controller->updateTranslation($matches[1]);
} elseif (preg_match('/^\/api\/translation\/(\d+)$/', $uri, $matches) && $method == 'DELETE') {
    $controller->deleteTranslation($matches[1]);
} else {
    header("HTTP/1.0 404 Not Found");
    echo json_encode(['status' => 'error', 'message' => 'Endpoint not found']);
}
