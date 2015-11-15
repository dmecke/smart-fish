[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](/LICENSE)
[![Build Status](https://img.shields.io/travis/dmecke/smart-fish.svg?style=flat-square)](https://travis-ci.org/dmecke/smart-fish)
[![Code Quality](https://img.shields.io/scrutinizer/g/dmecke/smart-fish.svg?style=flat-square)](https://scrutinizer-ci.com/g/dmecke/smart-fish/?branch=master)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/dmecke/smart-fish.svg?style=flat-square)](https://scrutinizer-ci.com/g/dmecke/smart-fish/?branch=master)
[![Project Status](https://img.shields.io/badge/status-evolving-brightgreen.svg?style=flat-square)](/)

# Neural Network with Genetic Algorithm

This code was originally a port of the tutorial source code written by "ai-junkie" Matt Buckland to be found at http://www.ai-junkie.com/ann/evolved/nnt1.html. If you are interested in AI buy his books. They are totally worth it, really!

![Screenshot](/doc/screenshot.jpg?raw=true)

## Installation

```bash
composer install
```

## Usage

For default settings use:

```bash
php app.php smartfish:run
```

To adjust the frames per second for the calculation use: (defaults to 30)

```bash
php app.php smartfish:run --fps=60
```

To adjust the number of ticks that are calculated per generation use: (defaults to 2000)

```bash
php app.php smartfish:run --ticks-per-generation=5000
```

All options can be combined of course.

## Testing

To run the testsuite use:

```bash
./vendor/bin/phpunit
```

To run the mutation testing use:

```bash
./vendor/bin/humbug
```
