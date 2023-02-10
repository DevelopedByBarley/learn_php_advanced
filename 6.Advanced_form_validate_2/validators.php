<?php
function required()
{
    return [
        "validatorName" => "required",
        "validatorFn" => function ($input) {
            return (bool)$input;
        },
        "params" => null
    ];
}

function choices(...$options)
{
    return [
        "validatorName" => "enum",
        "validatorFn" => fn ($input) => in_array($input, $options),
        "params" => implode(", ", $options)
    ];
}


function validateEmailFormat()
{
    return [
        "validatorName" => "email",
        "validatorFn" => fn ($input) => filter_var($input, FILTER_VALIDATE_EMAIL),
        "params" => null
    ];
}

function upperThan($lower)
{
    return [
        "validatorName" => "largerThan",
        "validatorFn" => fn ($input) => $input > $lower,
        "params" => $lower
    ];
}

function maxLength($length)
{
    return [
        "validatorName" => "maxLength",
        "validatorFn" => fn ($input) => strlen($input) < $length,
        "params" => $length
    ];
}


function between($lower, $upper) {
    return [
        "validatorName" => "between",
        "validatorFn" => function ($input) use ($lower, $upper) {
            return $input >= $lower && $input <= $upper;
        },
        "params" => [$lower, $upper]
    ];
}

