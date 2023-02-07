<?php

$routes = [
    'GET' => [
        '/learn_php_advanced/6.File_upload/server/' => 'homeHandler',
    ],
    'POST' => [
        '/learn_php_advanced/6.File_upload/server/upload-images' => 'uploadHandler',
    ],
];

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER["REQUEST_METHOD"];

$handlerFunction = $routes[$method][$path] ?? function () {
    http_response_code(404);
    echo "Oldal nem található";
};

$handlerFunction();

function homeHandler()
{
    echo render('home.phtml', []);
}



function uploadHandler()
{
    echo "<pre>";
    $files = transformToSingleFiles($_FILES["fajlok"]); 

    foreach($files as $file) {
        resizeAndSaveImage($file);
    }
}



function resizeAndSaveImage($file)
{
    $whiteList = [IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF];


    if(!in_array(exif_imagetype($file['tmp_name']), $whiteList)) return false;

    $rand = uniqid(rand(), true); // Random id
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION); // File kiterjesztéséhez való hozzájutás

    // A 2-ből már összeollózhatunk egy új filenevet

    $originalFileName = $rand . '.' . $ext;
    $directoryPath = "./public/images/";

    $isMoveSuccessful = file_put_contents($directoryPath . $originalFileName, file_get_contents($file["tmp_name"]) );

    if(!$isMoveSuccessful) {
        return false;
    }
}

function transformToSingleFiles($rawFiles)
{
    $ret = [];

    for ($i = 0; $i < count($rawFiles["name"]); $i++) {
        $ret[] = [
            "name" => $rawFiles["name"][$i],
            "type" => $rawFiles["type"][$i],
            "tmp_name" => $rawFiles["tmp_name"][$i],
            "error" => $rawFiles["error"][$i],
            "size" => $rawFiles["size"][$i],
        ];
    }

    return $ret;
}





function render($path, $params = [])
{
    ob_start();
    require __DIR__ . '/views/' . $path;
    return ob_get_clean();
}

function getConnection()
{
    return new PDO(
        'mysql:host=' . $_SERVER['DB_HOST'] . ';dbname=' . $_SERVER['DB_NAME'],
        $_SERVER['DB_USER'],
        $_SERVER['DB_PASSWORD']
    );
}
