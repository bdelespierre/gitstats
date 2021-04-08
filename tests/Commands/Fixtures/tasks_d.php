<?php

return [

    'tasks' => [
        'a' => [
            'command' => function ($commit) {
                return sprintf(
                    "int: %d, double: %d, square: %d",
                    $commit[0],
                    $commit[0] * 2,
                    $commit[0] ** 2,
                );
            },
            'patterns' => [
                "int" => '/int: (\d+)/',
                "double" => '/double: (\d+)/',
                "square" => '/square: (\d+)/',
            ],
        ],
        'b' => [
            'command' => function ($commit) {
                return $commit[0];
            },
            'patterns' => [
                "pair" => "/\d*[02468]$/",
            ],
        ],
    ],

];
