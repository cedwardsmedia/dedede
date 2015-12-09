#!/usr/bin/php
<?php

// Define values
   define("VERSION", "1.1.0"); // Dedede version
   define("MINPHPVER", "5.3"); // Minimum supported PHP version

// Disable warnings and notices
   error_reporting(E_ERROR | E_PARSE);

// Bootstrap check
   // Check our PHP version
   if (phpversion() < MINPHPVER) {
      echo("Kirby CMS requires PHP 5.3 or greater. You are currently running PHP " . phpversion() . ". Please upgrade PHP.\n");
      exit(1);
   }

   // Check for git
   if (!shell_exec("git --version")) {
      echo "Git not found. Dedede requires Git.\nIf you know Git is installed, please verify that it is in your \$PATH.\n";
      exit(1);
   }

   // Check for compatible OS
   if (PHP_OS != "Windows") {
      exit("Sorry, Dedede does not currently run on Windows.\n");
   }

// Check to see if a command has been issued
   // Commands are issued in array key 1
   if ( array_key_exists ("1", $_SERVER['argv']) ) {

      // Process flags

      // If user passes --version or -v
      if (in_array("--version", $_SERVER['argv']) || in_array("-v", $_SERVER['argv'])){
         version(); // Show version information

      // If user passes --help or -h
      } elseif (in_array("--help", $_SERVER['argv']) || in_array("-h", $_SERVER['argv'])){
         help(); // Show help
      } else {

         // If no valid flag was passed, consider it a command
         define("COMMAND", $_SERVER['argv'][1]);
      }
   }

// Determine our target path
   // Paths are issued in array key 2
   // If a path has been specified, let's set it to our constant
   if ( array_key_exists("2",$_SERVER['argv']) ) {
      $path = $_SERVER['argv'][2];

      if ($path[0] == DIRECTORY_SEPARATOR) {
         // User specified an absolute path, so let's use it
         define("PATH", $_SERVER['argv'][2]);
      } elseif (substr($path, 0, 2) == "./") {
         // User specified our current working directory. Let's sanitize the path for the sake of cleanliness and readability
         define("PATH", getcwd() . "/" . str_replace("./", "", $path));
      } elseif ($path == ".." || $path == "../" ) {
         // User specified the parent directory. We can't do this because the parent directory is obviously not empty. Let's err and die.
         echo "Cannot install Kirby to parent directory. Target must be empty or not exist.\n";
         exit(1);
      } elseif (substr($path, 0, 2) == ".." && (strlen($path) > 2)) {
         // User specified a sibling (or child of a sibling) directory. Let's resolve the ../ to the actual path for cleanliness and readability.
         define("PATH", dirname(getcwd()) . "/" . str_replace("../", "", $_SERVER['argv'][2]));
      } else {
         // User specified a child of the current working directory. Let's use it.
         define("PATH", getcwd() . "/" . $_SERVER['argv'][2]);
      }
   } else {
      // No path has been specified, so let's get the current working directory
      define("PATH", getcwd());
   }

// Process command
   // Each case corresponds to a specific command passed to dedede
   switch (COMMAND) {
      // Let's install Kirby!
      case 'install':
         install();
         break;

      // Let's update Kirby!
      case 'update':
         update();
         break;

      // Let's install the Panel!
      case 'panel':
         installpanel();
         break;

      // Let's print debug info!
      case 'debug':
         debug();
         break;

      // Let's make a donation!
      case 'donate':
         donate();
         break;

      // No command was passed, print usage
      default:
         usage();
         break;
   }

// Asks user a question and returns TRUE for YES or FALSE for NO
   function ask($Q) {
      echo "$Q [Y/N]: ";
      $response = trim(fgets(STDIN));

      if (strtoupper($response) == "Y" || strtoupper($response) == "YES"){
         // User confirmed Panel install. Let's init the submodule
         return TRUE;
      } elseif (strtoupper($response) == "N" || strtoupper($response) == "NO"){
         return FALSE;
      } else {
         // User did not enter Y or N
         echo "Please type Y or N.";
         ask($Q);
      }
   }
// This function is called when the install command is passed
   function install() {
   // Run precheck to prevent common errors
      precheck();

   // Confirm that the user wants to install Kirby to the chosen path
      if (ask("I will download Kirby to " . PATH ."\nIs that OK?")){
         doinstall();
      } else {
         exit;
      }
   }

// The meat of the script. Here, we actually download and install Kirby
   function doinstall(){

      clone_kirby();
      initialize_kirby();
      initialize_toolkit();

   // Ask if we want to keep the panel
      if (ask("Do you want to install the Kirby Panel?")){
      // User confirmed Panel install. Let's init the submodule
         echo "  - Initializing Kirby Panel...\r";
         shell_exec("git submodule --quiet init panel && git submodule --quiet update panel");
         if (shell_exec("git submodule status panel")) {
            echo "  ✓ \n\n";
         } else {
            echo "  ✗ \n\n";
            $error_panel = 1;
         }
      } else {
      // User refused Panel install. Let's remove the Panel submodule
         rmdir(PATH . "/panel");
      }

      echo "Success! Kirby has been installed to " . PATH . "\n";

      if ($error_panel) {
         echo "However, Dedede was unable to install the Kirby Panel.\nPlease run 'dedede debug' and open a new issue on GitHub.\n";
      }

   // Check for mbstring extension in PHP (required by Kirby)
      if (!extension_loaded('mbstring')) {
      // mbstring not found. Print a warning for the user
         echo "\nWarning: UTF-8 support in PHP not found. (mbstring extension not loaded.) Kirby will have problems with this.";
      }
      exit(0);
   }

// Git clone Kirby Starter Kit to PATH
   function clone_kirby() {
      echo "+ Working...\n";
      echo "  - Cloning Kirby...\r";
      shell_exec("git clone https://github.com/getkirby/starterkit " . PATH . " --quiet");

      chdir(PATH); // Change to PATH

      if (shell_exec("git status")) {
         echo "  ✓ \n";
      } else {
         echo "  ✗ \n\n";
         exit("Dedede was unable to clone Kirby.\nPlease run 'dedede debug' and open a new issue on GitHub.\n");
      }
   // Remove the Kirby origin
      shell_exec("git remote remove origin");
   }

// Initialize the Kirby System Folder submodule
   function initialize_kirby() {
      echo "  - Initializing Kirby system folder...\r";
      shell_exec("git submodule --quiet init kirby && git submodule --quiet update kirby");
      if (shell_exec("git submodule status kirby")) {
         echo "  ✓ \n";
      } else {
         echo "  ✗ \n\n";
         exit("Dedede was unable to initialize the Kirby system folder.\nPlease run 'dedede debug' and open a new issue on GitHub.\n");
      }
   }

// Initialize Kirby Toolkit
   function initialize_toolkit() {
      echo "  - Initizalizing Kirby toolkit...\r";
      chdir("kirby");
      shell_exec("git submodule --quiet init toolkit && git submodule --quiet update toolkit");
      if (shell_exec("git submodule status toolkit")) {
         echo "  ✓ \n";
      } else {
         echo "  ✗ \n\n";
         exit("Dedede was unable to initialize the Kirby toolkit.\nPlease run 'dedede debug' and open a new issue on GitHub.\n");
      }
      chdir(PATH);
   }

// Update Kirby using git submodules
   function update() {
      // Run precheck to prevent common errors
      precheck();
      if (!is_kirby(PATH)) {
         exit("Dedede cannot find a Kirby installation at " . PATH . "\n");
      } elseif (!is_git(PATH)){
         exit("\nIt seems Dedede did not install Kirby to " . PATH . "\nAlternatively, the Kirby installation wasn't cloned with git.\nDedede can only update Kirby installations installed with Dedede or cloned with git.\n\n");
      }
      // Confirm that we want to update Kirby at PATH
      if (ask("+ Do you want to update Kirby at " . PATH . "?")){
         doupdate(); // User confirmed update. Let's update Kirby!
      } else {
         exit; // User refused update. Let's exit.
      }
   }

// Install Kirby Panel using git submodules
   function installpanel() {
   // Run precheck to prevent common errors
      precheck();
      if (!is_kirby(PATH)) {
         exit("Dedede cannot find a Kirby installation at " . PATH . "\n");
      } elseif (!is_git(PATH)){
         exit("\nIt seems Dedede did not install Kirby to " . PATH . "\nAlternatively, the Kirby installation wasn't cloned with git.\nDedede can only update Kirby installations installed with Dedede or cloned with git.\n\n");
      }


   // Confirm that we want to install Kirby Panel at PATH
      if (ask("Kirby Panel will be installed to: " . rtrim(PATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . "panel\nIs that OK?")){
         echo "+ Working...\n  - Initializing Kirby Panel...\n";
         chdir(PATH);
         mkdir("panel");
         shell_exec("git submodule --quiet init panel && git submodule --quiet update panel");

         if (has_panel) {
            echo "Kirby Panel has been installed to " . PATH . "\n";
         }
      } else {
         exit; // User refused install. Let's exit.
      }
}

// Performs the actual update on Kirby by updating git submodules
   function doupdate() {
      echo "+ Working...\n";
      chdir(PATH); // Change to the Kirby project directory
   // Update the Kirby System Folder submodule
      echo "  - Updating Kirby system folder...\r";
      shell_exec("git submodule --quiet update kirby");
      echo "  ✓ \n";
   // Update Kirby Toolkit
      echo "  - Updating Kirby toolkit...\r";
      echo "  ✓ \n";
      chdir("kirby"); // Change to the Kirby directory
      shell_exec("git submodule --quiet update toolkit");
      chdir(PATH); // Change back to Kirby project directory

   // Update the Panel if it exists
      if (has_panel(PATH)) {
         echo "  - Updating Kirby Panel...\r";
         shell_exec("git submodule --quiet update panel");
         echo "  ✓ \n";
      }
      // We really need to validate the status before printing this
      echo "\nSuccess! Kirby has been updated at " . PATH . "\n";
      exit(0);
   }


// Perform various checks before installing or updating
   function precheck() {

      // Check if the target PATH exists
      if (file_exists(PATH)) {

         if (COMMAND == "install") {
            // Check if the target PATH is empty
            if (!is_empty(PATH)) {
               exit("Dedede cannot install Kirby to " .PATH . "\n" . PATH ." is not empty.\n");
            }
         }

         // Check of the target PATH is writeable
         if (!is_writable(PATH)) {
            exit("Dedede cannot write to " . PATH . "\n");
         }
      }

      // Check that we can connect to Github
      if (!fsockopen("github.com", 9418, $errno, $errstr, 5)) {
         exit("Dedede cannot connect to Github: $errstr ($errno)\nDedede must be able to connect to GitHub on port 9418 to download Kirby.\n");
      }
   }

// Check if directory is empty
   function is_empty($path) {
      if (count(scandir($path)) > 2) {
         return 0;
      } else {
         return 1;
      }
   }

// Check if directory is a Kirby installation
   function is_kirby($path) {
      if (file_exists($path. "/kirby/kirby.php")) {
         return 1;
      } else {
         return 0;
      }
   }

// Check if Kirby has panel
   function has_panel($path) {
      if (file_exists(PATH . "/panel/index.php")) {
         return 1;
      } else {
         return 0;
      }
   }
// Check if directory is a git repo
   function is_git($path) {
      if (file_exists(PATH . "/.git/config")) {
         return 1;
      } else {
         return 0;
      }
   }
// Print Debug information for developer
   function debug() {
      // Print General System Information
      echo "General Information:\n";
      echo " + Operating System: " . PHP_OS . "\n";
      $conn = fsockopen("github.com", 9418, $errno, $errstr, 5);
      echo " + GitHub connection: ";
      if (!$conn) {
         echo "$errstr ($errno)\n";
      } else {
         echo "Success!\n";
      }
      // Print Git Information
      echo "Git Information:\n";
      echo " + Git version: " . ltrim(shell_exec("git --version"), "git version ");
      echo " + Git binary: " . shell_exec("which git");

      // Print PHP Information
      echo "PHP Information:\n";
      echo " + PHP binary: " . PHP_BINARY . "\n";
      echo " + PHP version: " . PHP_VERSION . "\n";

      // Print Misc. Information
      echo "Misc. Information:\n";
      echo " + Target: " . PATH . "\n";
      echo " + Target writeable: ";
      if (is_writeable(PATH)) {echo "Yes\n";} else {echo "No\n";}
      echo " + Target Empty: ";
      if (is_empty(PATH)) {echo "Yes\n";} else {echo "No\n";}
      echo " + Target Kirby: ";
      if (is_kirby(PATH)) {echo "Yes\n";} else {echo "No\n";}
      echo " + Target Panel: ";
      if (has_panel(PATH)) {echo "Yes\n";} else {echo "No\n";}
      echo " + Target Git Repo: ";
      if (is_git(PATH)) {echo "Yes\n";} else {echo "No\n";}
      echo "\n\nPlease include the above output in your support request.\n";
   }

// Display donate odbc_setoption
   function donate() {
      if (PHP_OS == "Darwin") {
         shell_exec("open \"https://gratipay.com/~cedwardsmedia/\"");
      } elseif (PHP_OS == "Linux" && shell_exec("xdg-open")) {
         shell_exec("xdg-open \"https://gratipay.com/~cedwardsmedia/\"");
      } else {
         echo wordwrap("Dedede is a free and open source project. If you find it useful, please consider making a small donation to the developer via Gratipay: https://gratipay.com/~cedwardsmedia/", shell_exec('/usr/bin/tput cols'), "\n");
      }
   }
// Print simple help information for Dedede
   function help() {
      $helptext = <<<HELP
Dedede is a command line tool for creating and updating Kirby CMS installations.\n\nDedede can install the latest Kirby release to a directory of your choosing by executing `dedede install /path/to/install/kirby`. Dedede will download the latest Kirby release from GitHub and ask if you wish to install the Kirby Panel. By using Dedede, you can remove many of the, otherwise tedious, steps involved in setting up an easily updateable Kirby installation.\n\nDedede can update any Kirby installation that was cloned from GitHub or created using Dedede. Dedede uses git submodules to do this.\n\nDedede is a personal project that may or may not receive new features beyond this core functionality. Dedede was crafted by Corey Edwards (@cedwardsmedia) and is licensed under the MIT License.\n
HELP;
      echo wordwrap($helptext, shell_exec('/usr/bin/tput cols'), "\n");
      exit(0);
   }

// Print usage information
   function usage() {
      $usagetext = <<<USAGE
Usage: dedede [command] /path/to/project

  + Available commands:
    - install => Dedede will install a fresh copy of Kirby to the specified path.
      Example: dedede install /home/cedwardsmedia/kirby

    - update => Dedede will update the existing copy of Kirby at the specified path.
      Example: dedede update /home/cedwardsmedia/kirby-outdated

  + Caveats:
    - Dedede does not currently handle errors encountered during git processes.
      (This will be fixed very soon.)
    - When installing Kirby, the path must either not exist or must be empty.
    - Dedede can not update a non-git (or non-Dedede installed) copy of Kirby.

  + Notes:
    - Dedede officially supports Kirby 2.2+. However, it should work for all of 2.x\n

  + Donate:
    - Dedede is a free and open source project. If you find it useful, please consider making a small donation to the developer via Gratipay: https://gratipay.com/~cedwardsmedia/
USAGE;
      echo wordwrap($usagetext, shell_exec('/usr/bin/tput cols'), "\n");
      exit(0);
   }

// Print version information
   function version() {
      echo "Dedede v" . VERSION . "\n";
      exit(0);
   }
