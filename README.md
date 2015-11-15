[![Build Status](https://travis-ci.org/dmecke/smart-fish.svg)](https://travis-ci.org/dmecke/smart-fish)

# Neural Network with Genetic Algorithm

This code was originally a port of the tutorial source code written by "ai-junkie" Matt Buckland to be found at http://www.ai-junkie.com/ann/evolved/nnt1.html. If you are interested in AI buy his books. They are totally worth it, really!

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
