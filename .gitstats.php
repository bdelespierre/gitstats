<?php

return [

    'tasks' => [
        'Commit message' => "git log -1 --pretty=%B | head -n 1",
        'Commit author' => "git log -1 --pretty=%an",
        'Number of files' => "find . -type f | wc -l",
        'Number of directories' => "find . -type d | wc -l",
    ],

];
