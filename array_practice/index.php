<?php


$arr = ["asd", "wasd", "lasd"];

echo "<pre>";
var_dump($arr);
echo "<hr>";

$ret = [];
foreach($arr as $key => $item) {
    $ret[$item] = [];
}

var_dump($ret);


$arr_2 = [
    [
        "name" => "testName",
        "password" => "testPassword"
    ],
    [
        "name" => "testName2",
        "password" => "testPassword2"
    ],
    [
        "name" => "testName3",
        "password" => "testPassword4"
    ],
];


$ret_2 = [];
foreach ($arr_2 as $key => $item) {
    $ret_2[$item["name"]] = [
        "name" => $item["name"],
        "password" => $item["password"]
    ];
}

