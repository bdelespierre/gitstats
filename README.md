# Iterate through git commits to gather statistics

## Installation

```
composer global require bdelespierre/gitstats
```

## Usage

- Add a `.gitstats.php` file in your project root directory:

```php
<?php

return [
    'tasks' => [
        'Commit message' => "git log -1 --pretty=%B | head -n 1",
        'Commit author' => "git log -1 --pretty=%an",
        'Number of files' => "find . -type f | wc -l",
        'Number of directories' => "find . -type d | wc -l",
    ],
];
```

- Run the application:

```shell
$ gitstats run
```

The output is formatted as CSV:

```csv
commit,date,"Commit message","Commit author","Number of files","Number of directories"
0e75bcac756226986f9e6ba745c0f1944ee482db,"2021-04-01 12:40:04","Major refactoring","Benjamin Delespierre",1647,398
1cd263613b1b3bb96bff86a04c0e0c42c9427f32,"2018-01-14 11:15:16","Add progress screenshot","Matthieu Napoli",1649,396
3159438bd963174acac8518d9d58e85fc5fb431f,"2018-01-10 11:48:56","Restrict dependencies correctly","Matthieu Napoli",1649,396
2dd0cf355552553eebc3614ada24c305393c628c,"2018-01-10 11:48:09","Show a progress bar","Matthieu Napoli",1649,396
a731d6c9d91c8e4f07db0bec6e22c912a55baef2,"2017-10-22 18:02:03","MIT License","Matthieu Napoli",1649,396
...
```

You can write the output to a file:

```shell
$ gitstats run > gistats.csv
```

