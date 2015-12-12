![Dedede Changelog](https://cdn.cedwardsmedia.com/images/dedede/dededelogo.png "Dedede Logo")

# Change Log
All notable changes to this project will be documented in this file.
This project (mostly) adheres to [Semantic Versioning](http://semver.org/). The notable exception is the first stable release being marked as **1.0.0**.

## [Work in Progress]
### Added
- Introduced `buy` command to open the Buy Kirby page.

### Changed
- Moved project history to CHANGELOG.md


## [1.1.1] - Dec 9, 2015

#### Added
- Introduced donate command to allow user to make donation to the developer.
- Introduced `clearcache` command to flush the Kirby cache during development. Dedede will automatically flush Kirby's cache after an update.
- Added OS detection to prevent failing attempts to operate on Windows (Dedede currently utilizes a few UNIX-only commands that prevents Windows compatibility)


#### Changed
- Cleaned up code to adhere to better conventions and to ease the possible future release of a Windows-compatible version.
- Rewrote `usage()` and `help()` to use heredoc format for readability.


#### Fixed
- Fixed Issue #1 where opening an existing target directory in Finder on OS X would prevent an install due to the creation of the .DS_Store file. Dedede now removes this file when the `install` command is given.
- Fixed definitions of constants to prevent PHP errors on some builds of PHP

### [1.1.0] - Dec 6, 2015

#### Added
- Introduced `debug` command for printing out various information useful for troubleshooting and debugging real-world user scenarios.
- Introduced `panel` command for installing the Kirby Panel to a git-based (or Dedede-installed) copy of Kirby where the panel does not already exist.
- Introduced new internal functions and consolidated previously reused code to optimize Dedede.
- Added more error handlers to reduce gibberish for non-dev users.
- Added full status checking for the install process. Dedede prints a status report following each step, considering failure to clone the starterkit or initalize the Kirby system folder or toolkit to be fatal errors. Dedede will not err out if the Panel fails to initalize, but will warn the user of the failure.


#### Changed
- Relaxed Y/N input to accept case-insensitive "yes" and "no" entries.
- Updated the installing/updating display to reflect the new status reports.


#### Fixed
- Fixed a bug where Dedede would attempt to update Panel even if it wasn't installed, thus printing an error to the screen.
- Fixed a bug where Dedede would not properly check for Kirby before attempting to update.



### [1.0.1] - Dec 4, 2015

#### Added
- Dedede now checks to see if the target path exists and is empty. This will prevent most (if not all) git-based errors Dedede encounters.
- Dedede now checks connectivity with Github.com before attempting to clone or update Kirby. If we can't connect, we can't install or update, right?


### [1.0.0] - Nov 22, 2015
#### Added
- Introduced `install` and `update` commands.
- Introduced `-v` & `--version` and `-h` & `--help` flags to print the version and help, respectively.
- Running Dedede without a command now prints usage information.
- Added sanity checks for path argument. (For handling `./`, `../`, etc.)


### [0.0.1] - Nov 18, 2015
- Initial stable release
