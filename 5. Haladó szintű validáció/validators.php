<?php
function maxLength($length) {
    return [
        "validatorNm" => "maxLength",
        "validatorFn" => fn ($input) => strlen($input) < $length, 
        "params" => $length 
    ];
}


function between($min, $max)
{
    return [
        "validatorNm" => "between",
        "validatorFn" => function($input) use($min, $max) {
            return $input >= $min && $input <= $max;
        },
        "params" => [$min, $max]
    ];
}


function emailValidator() {
    return [
        "validatorNm" => "emailValidator",
        "validatorFn" => fn ($input) => (filter_var($input, FILTER_VALIDATE_EMAIL)),
        "params" => null
    ];
}


function greaterThan($min) {
    return [
        "validatorNm" => "greaterThan",
        "validatorFn" => fn ($input) => $input > $min,
        "params" => null
    ];
}




function required()
{
    return [
        "validatorNm" => "required",
        "validatorFn" => function ($input) {
            return (bool)$input;
        },
        "params" => null
    ];
};

function choseOptions(...$options) {
    return [
        "validatorNm" => "choseOptions",
        "validatorFn" => fn ($input) => in_array($input, $options), 
        "params" => implode(", ", $options)
    ];
}
