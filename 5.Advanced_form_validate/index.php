<?php

require './validators.php';

$routes = [
    'GET' => [
        '/' => 'homeHandler',
    ],
    'POST' => [
        '/new-employee' => 'newEmployeeHandler',
    ],
];

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER["REQUEST_METHOD"];

$handlerFunction = $routes[$method][$path] ?? function () {
    http_response_code(404);
    echo "Oldal nem található";
};

$handlerFunction();

function newEmployee() {
    $employerSchema = [
        "name" => [required(), maxLength(30)],
        "role" => [required(), choices("manager", "leader", "worker")],
        "age" => [required(), between(18, 90)],
        "salary" => [required(), upperThan(0)],
        "email" => [required(), validateEmailFormat()],
        "isVerified" => [required()],
    ];

    return toSchema($employerSchema);
}
// 1. Jelenleg ha ki dumpoljuk a schema-t akkor egy asszociativ tömböt kapunk vissza amelyben felsorakoznak a kulcs értékpárok
// Ez az értékpráok szintén asszociativ tömbök amelyek tartalmazzák a validate functionöket

/**
 *  Jelenleg igy néz ki a struktúra
 * 
 *  "name" => [0] => [required],
 *            [1] => [maxLength]
 * 
 *  "Ebből szeretnénk eltüntetni az indexet, tehát a kívánt eredmény ez lenne "
 *      "name" => [required] => [required]
 *             => [maxLength] = [maxLength]
 * 
 * 2. Ezért létrehozunk egy struktúra átalakító functiont toSchema néven!
 */

var_dump(newEmployee());

function toSchema($employer)
{
    echo "<pre>";

    $ret = [];
    foreach ($employer as $key => $schemas) {
        foreach ($schemas as $schema) {
            $ret[$key] = [
                $schema["validatorNm"] => $schema
            ];
        }
    };
    return $ret;
}

/**
 *         $ret[$key] = [
           $schemas["validatorNm"] => []
        ];
 */



function homeHandler()
{
    echo render("home.phtml", [
        "isSuccess" => true
    ]);
}

function newEmployeeHandler()
{
    echo "<pre>";
    var_dump($_POST);
}

function render($path, $params = [])
{
    ob_start();
    require __DIR__ . '/views/' . $path;
    return ob_get_clean();
}
