<?php
require "./vendor/autoload.php";

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
$twig = new Environment(new FilesystemLoader('./views'));


$routes = [
    'GET' => [
        '/' => 'homeHandler',
    ],
];

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER["REQUEST_METHOD"];

$handlerFunction = $routes[$method][$path] ?? "notFoundHandler";

$handlerFunction($twig);


function homeHandler($twig)
{
    $content = file_get_contents(__DIR__ . "/store/recipes.json");
    $recipes = json_decode($content, true);

    echo $twig->render("wrapper.twig", [
        "content" => $twig->render("recipe-list.twig", [
            "recipes" => $recipes
        ]), 
    ]); 
};

function notFoundhandler()
{
    http_response_code(404);
    echo render("wrapper.phtml", [
        "content" => render("404.phtml")
    ]);
}

function render($filePath, $params = []): string
{
    ob_start();
    require __DIR__ . "/views/" . $filePath;
    return ob_get_clean();
}

function toTime($t)
{
    $start = new DateTime();
    $end = new DateTime();
    $end->setTimestamp($t);
    $interval = new DateInterval("P1D");
    $range = new DatePeriod($start, $interval, $end);
    return $range->end->format("i:s");
}

function welcome($wtf) {
    
}
