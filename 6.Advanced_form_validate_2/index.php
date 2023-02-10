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

function homeHandler()
{

    if (isset($_GET["errors"])) {
        $decodedErrors =  json_decode(base64_decode($_GET["errors"]), true);
        var_dump($decodedErrors);
    }
    echo render("home.phtml", [
        "isSuccess" => true
    ]);
}

function employeeSchema()
{
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



function toSchema($employer)
{
    echo "<pre>";

    $ret = [];

    foreach ($employer as $key => $items) {
        foreach ($items as $item) {
            $ret[$key][$item["validatorName"]] = $item;
        }
    }




    return $ret;
}




function newEmployeeHandler()
{

    $errors = validate(employeeSchema(), $_POST);



    $encodedErrors = base64_encode(json_encode($errors)); // A vissza kapott errorokat encode-oljuk először json-be majd base64-be, hogy el tudjuk küldeni az url-be


    // Checkoljuk hogy volt-e error
    foreach ($errors as $error) {
        if (!empty($error)) {
            header("Location: /?errors=" . $encodedErrors);
            return;
        }
    }

    // Ha nem akkor ez a header
    header("Location: /?isSuccess=1");


    // Következő lépés , hogy decodeoljuk a frontnak az encode-olt error-okat

}
// I. megírjuk a validáló functiont ami a beérkező user adatokat összebveti a validáló függvényekkel, majd visszatér

function validate($schema, $body)
{


    $fieldNames = array_keys($schema); // Ki kérjük ennek a schemanak a kulcsait
    // Így egy tömböt kapunk mint [name, role, age, salary];
    // Ebből szeretnénk egy asszociativ tömböt késziteni amelyben a "kulcsok" a $fieldNames tömbben felsorolt érték lesznek,  az "értékek" pedig egy üres tömbök
    // Ennek segitségével fogjuk elérni azt hogy az adott érték üres tömbjét feltöltsük az aktuális hibákkal reprezentált értékekkel


    $ret = [];
    foreach ($fieldNames as  $fieldName) {
        $ret[$fieldName] = [];
    }
    // Ezzel elértük az átalakítást..
    // Most már csak annyi a dolgunk, hogy ezeket a tömböket feltöltsük az esetleges hibákkal....


    // II. Ezek után a célunk már csak az , hogy elérjük a function-ököte a validátorokban és meghívjuk azt bepasszolva neki a beérkező adatokat;
    foreach ($fieldNames as $fieldName) {  // Át iterálunk a fieldName-eken amik array_keys()-el lettek kikérve;
        $validators = $schema[$fieldName]; // Majd ezzel validátor néven kikérhetjük teljes schemaból a teljes validátorok tömbjét
        foreach ($validators as $validator) { // Ezeken a validátorokon végig iterálunk
            $validatorFn = $validator["validatorFn"]; // És validatorFn névre kiszervezzük a $validator tömbök $validator functionét 
            $isFieldValid = $validatorFn($body[$fieldName] ?? null); // Amit már meg tudunk hivni úgy hogy bepasszoljuk neki az érkező $body értékeit úgy h a $_POST global-ból a fieldname [name, role.. stb] segítségével kiszedjük az adatokat
            // Ha ezt a $validatorFn-t meghívjuk, és elküldjük a formot, akkor a validátor bool értékeket küld vissa annak függvényében, hogy mit validált
            // Már csak annyi a dolgunk , hogy a kiszámolt bool értékek függvényében feltöltjük a ret változóban leírt hibákat jelölő üres tömböket.

            if (!$isFieldValid) { // Tehát a felsorolt boolt értékekből valamelyik nem valid
                $ret[$fieldName][] = [ // Akkor pusholjuk hozzá a már létrehozott ret változó fieldName kulcs alatti tömbjéhez a következőt
                    "validatorName" => $validator['validatorName'], // Validator name validatorokon átiterált validatornevét
                    'value' => $body[$fieldName] ?? null // és a bodyban fieldname alatt létező  értékét
                ];
            };
        }
    }

    // A következő lépés hogy ezeket a szeretnénk elküldeni kliens olralra! ez a newEmployeeHandler-ben tesszük meg 
    return $ret;
}




function render($path, $params = [])
{
    ob_start();
    require __DIR__ . '/views/' . $path;
    return ob_get_clean();
}
