![Dedede v1.0](https://cdn.cedwardsmedia.com/images/dedede/dededelogo.png "Dedede Logo")


[![Source](https://img.shields.io/badge/source-cedwardsmedia/dedede-blue.svg?style=flat-square "Source")](https://www.github.com/cedwardsmedia/dedede)
![Version](https://img.shields.io/badge/version-1.0-brightgreen.svg?style=flat-square)
[![License](https://img.shields.io/badge/license-MIT-lightgrey.svg?style=flat-square "License")](./LICENSE)
[![Gratipay](https://img.shields.io/gratipay/cedwardsmedia.svg?style=flat-square "License")](https://gratipay.com/~cedwardsmedia/)

Made for **Kirby** with **♥**

_Dedede_ is a command line script for creating a [Kirby CMS](http://www.getkirby.com/) project. It automates the repetitive tasks of cloning the Kirby git repo, initializing the submodules, etc. Dedede was originally written to automate my workflow to make life easier and allow me to spend my time where it belongs - building the website, not preparing the project.

## Requirements
 - PHP 5.3
 - Git

**Note**: Dedede only requires PHP and Git to work. It will throw an error if you are running anything earlier than PHP 5.3. As Dedede is intended for use during development and design of a Kirby-based project, Dedede does not (and likely never will) check for Apache 2 with mod_rewrite or NGINX.

## Installation

1. Clone the repo.
2. Ensure dedede.php is executable by running `chmod +x /path/to/dedede.php`
3. - Run dedede by executing `php dedede.php [command] [path]` OR
   - Create an symbolic link to Dedede by running `sudo ln -s /path/to/dedede.php /usr/local/dedede`. _The remainder of this document assumes this approach._

## Usage

#### Installing Kirby
- `cd` to the existing **empty** directory you want to use for your project and run `dedede install`. Dedede will download Kirby to the current working directory. Thus, this directory **MUST** be empty.
- You can run `dedede install /path/to/project` to have Dedede download Kirby to the specified path, creating it if it does not exist. Dedede accepts relative and absolute paths.

#### Updating Kirby
- `cd` to the existing Kirby project and run `dedede update`. Dedede will update the Kirby installation in the current working directory.
- You can run `dedede update /path/to/project` to have Dedede update the Kirby installation at the specified path. Dedede accepts relative and absolute paths.

## Contributing

1. Fork it!
2. Create your feature branch: `git checkout -b my-new-feature`
3. Commit your changes: `git commit -am 'Add some feature'`
4. Push to the branch: `git push origin my-new-feature`
5. Submit a pull request ^^,

## History

 - **[_Nov 22, 2015_]: 1.0** Major internal changes.
   - Introduced `install` and `update` commands.
   - Introduced `-v` & `--version` and `-h` & `--help` flags to print the version and help, respectively.
   - Running Dedede without a command now prints usage information.
   - Added sanity checks for path argument. (For handling `./`, `../`, etc.)


 - [_Nov 18, 2015_]: 0.1 Initial stable release

## To-do:

1. Add ability to check various parameters of existing Kirby installation
2. Check target path before installing or updating.
3. Add ability to "git-ify" non-git-based Kirby installation. (i.e. one downloaded explicitly as ZIP file from Kirby site)
4. Add error handling.
5. Think of something else to add here.


## Credits
Concept and original codebase: Corey Edwards ([@cedwardsmedia](https://www.twitter.com/cedwardsmedia))

## License
_Dedede_ is licensed under the **MIT License**. See LICENSE for more.

---
**Disclaimer**: _Dedede_ is not endorsed by, sponsored by, or otherwise associated with [Kirby](http://www.getkirby.com).
