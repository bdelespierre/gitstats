# GitStats

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

Rewinds your Git history to compute stats.

## Installation

```bash
composer global require bdelespierre/gitstats
```

<details><summary>Add composer global vendor/bin directory to your PATH</summary>

Best way to do it is to add these lines to your `~/.profile`:

```bash
# Composer 1 global vendor/bin to PATH
if [ -d "$HOME/.composer/vendor/bin" ] ; then
    PATH="$PATH:$HOME/.composer/vendor/bin"
fi

# Composer 2 global vendor/bin to PATH
if [ -d "$HOME/.config/composer/vendor/bin" ] ; then
    PATH="$PATH:$HOME/.config/composer/vendor/bin"
fi

export PATH
```

</details>

## Usage

Add a `.gitstats.php` file in your project root directory:

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

Run the application:

```bash
gitstats run
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

```bash
gitstats run > gistats.csv
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email :author_email instead of using the issue tracker.

## Credits

- [Benjamin Delespierre][link-author-bdelespierre]
- [Matthieu Napoli][link-author-mnapoli]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/bdelespierre/gitstats.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/bdelespierre/gitstats/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/bdelespierre/gitstats.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/bdelespierre/gitstats.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/bdelespierre/gitstats.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/bdelespierre/gitstats
[link-travis]: https://travis-ci.org/bdelespierre/gitstats
[link-scrutinizer]: https://scrutinizer-ci.com/g/bdelespierre/gitstats/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/bdelespierre/gitstats
[link-downloads]: https://packagist.org/packages/bdelespierre/gitstats
[link-author-bdelespierre]: https://github.com/bdelespierre
[link-author-mnapoli]: https://github.com/mnapoli
[link-contributors]: ../../contributors
