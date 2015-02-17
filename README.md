# MODX Shell

A CLI application wrapper for MODX Revolution.


## Goals

* Be able to run most relevant processors from the CLI
* Allow third party components to ship CLI commands
* Allow developers to build their own set of commands


## Requirements

* PHP CLI 5.3.9+
* Composer


## Installation

1. Clone the repository `git clone https://github.com/meltingmedia/MODX-Shell.git`
2. Install dependencies `cd MODX-Shell && composer install --no-dev`
3. Optionally add `bin/modx` to your `$PATH` so you could use `modx` command from anywhere, by running `ln -s /path/to/bin/modx ~/bin/modx`

Running `modx` should then output the available commands.


## Documentation

Documentation can be found at <https://docs.melting-media.com/modx-shell/>.


## Roadmap

A road map can be found at <https://github.com/meltingmedia/MODX-Shell/milestones>


## License

MODX Shell is licensed under the [MIT license](LICENSE.md).
Copyright 2013 Melting Media <https://github.com/meltingmedia>
