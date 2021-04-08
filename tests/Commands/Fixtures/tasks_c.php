<?php

return [

    'tasks' => [
        'a' => function (string $commit) {
            return substr($commit, 0, 8);
        },
    ],

];
