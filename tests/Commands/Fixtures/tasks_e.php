<?php

return [

    'tasks' => [
        'a' => [
            'command' => function ($commit) {
                return $commit;
            },
            'patterns' => [
                "invalid" => "%",
            ],
        ],
    ],

];
