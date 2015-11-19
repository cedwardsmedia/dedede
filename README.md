# Dedede v1.0

[![Source](https://img.shields.io/badge/source-cedwardsmedia/dedede-blue.svg?style=flat-square "Source")](https://www.github.com/cedwardsmedia/dedede)
![Version](https://img.shields.io/badge/version-1.0-brightgreen.svg?style=flat-square)
[![License](https://img.shields.io/badge/license-MIT-lightgrey.svg?style=flat-square "License")](./LICENSE)
[![Gratipay](https://img.shields.io/gratipay/cedwardsmedia.svg?style=flat-square "License")](https://gratipay.com/~cedwardsmedia/)

_Dedede_ is a command line script for creating a [Kirby CMS](http://www.getkirby.com/) project. It automates the repetitive tasks of cloning the Kirby git repo, initializing the submodules, etc. Dedede was originally written to automate my workflow to make life easier and allow me to spend my time where it belongs - building the website, not preparing the project.

## Requirements
 - PHP 5.3
 - Git

**Note**: Dedede only requires PHP and Git to work. It will throw an error if you are running anything earlier than PHP 5.3. As Dedede is intended for use during development and design of a Kirby-based project, Dedede does not (and likely never will) check for Apache 2 with mod_rewrite or NGINX.

## Installation

1. Clone the repo.
2. Create an symbolic link to Dedede by running `sudo ln -s /path/to/dedede.php /usr/local/dedede`.

## Usage
You can run Dedede two ways.

1. `cd` to the existing **empty** directory you want to use for your project and run `dedede`. Dedede will download Kirby to the current working directory. Thus, this directory **MUST** be empty.
2. You can run `dedede /path/to/project` to have Dedede download Kirby to the specified path, creating it if it does not exist. Dedede accepts relative and absolute paths.

## Contributing

1. Fork it!
2. Create your feature branch: `git checkout -b my-new-feature`
3. Commit your changes: `git commit -am 'Add some feature'`
4. Push to the branch: `git push origin my-new-feature`
5. Submit a pull request ^^,

## History

 - **[_Nov 18, 2015_]: 1.0** Initial stable release

## To-do:

1. Think of something to add here

## Credits
Concept and original codebase: Corey Edwards ([@cedwardsmedia](https://www.twitter.com/cedwardsmedia))

## License
_Dedede_ is licensed under the **MIT License**. See LICENSE for more.

---
**Disclaimer**: _Dedede_ is not endorsed by, sponsored by, or otherwise associated with [Kirby](http://www.getkirby.com).
