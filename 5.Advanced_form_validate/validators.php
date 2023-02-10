<?php
function required()
{
    return [
        "validatorNm" => "required",
        "validatorFn" => function ($input) {
            return (bool)$input;
        },
        "params" => null
    ];
}

function maxLength($length)
{
    return [
        "validatorNm" => "required",
        "validatorFn" => fn ($input) => strlen($input) < $length, 
        "params" => $length
    ];
}

function upperThan($min) {
    return [
        "validatorNm" => "required",
        "validatorFn" => fn ($input) => $input > $min, 
        "params" => $min
    ];
}


function validateEmailFormat() {
    return [
        "validatorNm" => "validateEmailFormat",
        "validatorFn" => fn ($input) => $input > filter_var($input, FILTER_VALIDATE_EMAIL), 
        "params" => null
    ];
}


function between($min, $max)
{
    return [
        "validatorNm" => "between",
        "validatorFn" => fn ($input) => $input >= $max && $input <= $min,
        "params" => [$min, $max]
    ];
}

function choices(...$options)
{
    return [
        "validatorNm" => "choices",
        "validatorFn" => fn ($input) => in_array($input, $options),
        "params" => implode(", ", $options)
    ];
}
