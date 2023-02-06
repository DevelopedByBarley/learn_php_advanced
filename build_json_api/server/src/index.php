<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

require './vendor/autoload.php';
require '../../db/getConnection.php';


$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/learn_php_advanced/build_json_api/server/', 'homeHandler');
    $r->addRoute('GET', '/learn_php_advanced/build_json_api/server/api/instruments', 'getAllInstrumentsHandler');
    $r->addRoute('GET', '/learn_php_advanced/build_json_api/server/api/instruments/{id}', 'getSingleInstrumentHandler');
    $r->addRoute('POST', '/learn_php_advanced/build_json_api/server/api/instruments', 'createInstrumentHandler');
    $r->addRoute('PATCH', '/learn_php_advanced/build_json_api/server/api/instruments/{id}', 'patchInstrumentHandler');
    $r->addRoute('DELETE', '/learn_php_advanced/build_json_api/server/api/instruments/{id}', 'deleteInstrumentHandler');
});



function getInstrumentById($id)
{
    $pdo = getConnection();
    $statement = $pdo->prepare("SELECT * FROM `instruments` WHERE id = ?");
    $statement->execute([$id]);
    $instrument = $statement->fetch(PDO::FETCH_ASSOC);
    return $instrument;
}

function getNotFoundByIdError($id)
{
    http_response_code(404);
    echo json_encode([
        'error' => [
            'id' => $id,
            'message' => 'invalid instrument id'
        ]
    ]);
}




function homeHandler()
{
    require './build/index.html';
}





function getAllInstrumentsHandler()
{
    header('Content-type: application/json');
    $pdo = getConnection();
    $statement = $pdo->prepare("SELECT * FROM `instruments`");
    $statement->execute();
    $instruments = $statement->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($instruments);
};

function getSingleInstrumentHandler($vars)
{
    header('Content-type: application/json');

    $instrument = getInstrumentById($vars["id"]);

    if (!$instrument) {
        getNotFoundByIdError($vars["id"]);
        return;
    }

    echo json_encode($instrument);
}




function createInstrumentHandler()
{
    header('Content-type: application/json');
    $body = json_decode(file_get_contents('php://input'), true);

    $pdo = getConnection();
    $statement = $pdo->prepare(
        "INSERT INTO `instruments` 
        (`name`, `description`, `brand`, `price`, `quantity`) 
        VALUES 
        (?, ?, ?, ?, ?)"
    );
    $statement->execute([
        $body['name'] ?? "",
        $body['description'] ?? "",
        $body['brand'] ?? "",
        (int)$body['price'] ?? null,
        (int)$body['quantity'] ?? null,
    ]);

    $id = $pdo->lastInsertId(); // Utoljára hozzáadott rekord id-ja visszakérhető insert után!
    $lastInstrument = getInstrumentById($id);

    echo json_encode($lastInstrument);
}


function deleteInstrumentHandler($vars)
{
    header('Content-type: application/json');

    $pdo = getConnection();
    $statement = $pdo->prepare("DELETE FROM `instruments` WHERE id = ?");
    $statement->execute([$vars["id"]]);

    if (!$statement->rowCount()) { // Statement rowcount() methodja azt mutatja meg, hogy az adott lekérdezés hány sort/oszlopot érintett, ha 1-et se, akkor hiba történt
        getNotFoundByIdError($vars["id"]);
        return;
    };
    exit;
    echo json_encode([
        "id" => $vars["id"]
    ]);
}



// !!! A PATCH METÓDUS ENGEDÉLYEZI AZ ÖSSZEOLVASZTÁST, MIG A PUT METHOD NEM ÉS TELJESEN FELÜLIR !!!!!
function patchInstrumentHandler($vars)
{
    header('Content-type: application/json');
    $instrument = getInstrumentById($vars["id"]);
    if (!$instrument) {
        getNotFoundByIdError($vars["id"]);
        return;
    }
    $body = json_decode(file_get_contents('php://input'), true);


    $pdo = getConnection();
    $statement = $pdo->prepare(
        "UPDATE `instruments` SET 
        `name` = ?,
        `description` = ?,
        `brand` = ?,
        `price` = ?,
        `quantity` = ?
        WHERE `id` = ?
        "
    );

    $statement->execute([
        $body["name"] ?? $instrument["name"],
        $body["description"] ?? $instrument["description"],
        $body["brand"] ?? $instrument["brand"],
        (int)$body["price"] ?? $instrument["price"],
        (int)$body["quantity"] ?? $instrument["quantity"],
        $vars["id"]
    ]);


    echo json_encode(getInstrumentById($vars["id"]));
}













// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        $handler($vars);
        break;
}
