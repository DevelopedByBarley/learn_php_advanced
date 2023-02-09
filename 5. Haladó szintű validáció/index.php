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

function homeHandler()
{
    echo render("home.phtml", [
        "isSuccess" => true
    ]);
}

$handlerFunction();

function employerSchema()
{

    $employerSchema = [
        "name" => [required(), maxLength(30)],
        "role" => [required(), choseOptions("manager", "leader", "worker")],
        "age" => [required(), between(18, 120)],
        "salary" => [required(), greaterThan(0)],
        "email" => [emailValidator()],
        "isVerified" => [required()],
    ];

    return toSchema($employerSchema);
}



function toSchema($items)
{
    $ret = [];
    foreach ($items as $key => $item) {
        foreach ($item as $innerItem) {
            $ret[$key][$innerItem["validatorNm"]] = $innerItem;
        }
    }

    return $ret;
}



function newEmployerHandler()
{
    validate(employerSchema(), $_POST);
}
function validate($schema, $body)
{
    $fieldNames = $schema;
    exit;
}

function render($path, $params = [])
{
    ob_start();
    require __DIR__ . '/views/' . $path;
    return ob_get_clean();
}
