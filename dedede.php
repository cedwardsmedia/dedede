#!/usr/bin/php
<?php

// Define values
define(VERSION, "1.1.0"); // Dedede version
define(MINPHPVER, "5.3"); // Minimum supported PHP version

// Disable error reporting
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
      define(COMMAND, $_SERVER['argv'][1]);
   }
}

// Determine our target path
// Paths are issued in array key 2
// If a path has been specified, let's set it to our constant
if ( array_key_exists("2",$_SERVER['argv']) ) {
   $path = $_SERVER['argv'][2];

   if ($path[0] == DIRECTORY_SEPARATOR) {
      // User specified an absolute path, so let's use it
      define(PATH, $_SERVER['argv'][2]);
   } elseif (substr($path, 0, 2) == "./") {
      // User specified our current working directory. Let's sanitize the path for the sake of cleanliness and readability
      define(PATH, getcwd() . "/" . str_replace("./", "", $path));
   } elseif ($path == ".." || $path == "../" ) {
      // User specified the parent directory. We can't do this because the parent directory is obviously not empty. Let's err and die.
      echo "Cannot install Kirby to parent directory. Target must be empty or not exist.\n";
      exit(1);
   } elseif (substr($path, 0, 2) == ".." && (strlen($path) > 2)) {
      // User specified a sibling (or child of a sibling) directory. Let's resolve the ../ to the actual path for cleanliness and readability.
      define(PATH, dirname(getcwd()) . "/" . str_replace("../", "", $_SERVER['argv'][2]));
   } else {
      // User specified a child of the current working directory. Let's use it.
      define(PATH, getcwd() . "/" . $_SERVER['argv'][2]);
   }
} else {
   // No path has been specified, so let's get the current working directory
   define(PATH, getcwd());
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

   // Let's print debug info
   case 'debug':
      debug();
      break;

   // No command was passed, print usage
   default:
      usage();
      break;
}


// This function is called when the install command is passed
function install() {

   precheck();

   // Confirm that the user wants to install Kirby to the chosen path
   echo "I will download Kirby to " . PATH ."\nIs that OK? [Y/N]: ";
   $response = trim(fgets(STDIN));

   // Validate Response
   if ($response == "Y"){
      // User confirmed. Let's proceed!
      doinstall();
   } elseif ($response == "N"){
      // User had a change of heart. Let's die.
      exit;
   } else {
      // User did not specify Y or N
      echo "Please type Y or N.\n";
      install();
   }
}

// The meat of the script. Here, we actually download and install Kirby
function doinstall(){

   install_kirby();

   initialize_kirby();

   initialize_toolkit();

   // Ask if we want to keep the panel
   echo "+ Do you want to install the Kirby Panel? [Y/N]: ";
   $response = trim(fgets(STDIN));

   if ($response == "Y"){
      // User confirmed Panel install. Let's init the submodule
      echo "  - Initializing Kirby Panel...\n";
      shell_exec("git submodule --quiet init panel && git submodule --quiet update panel");
   } elseif ($response == "N"){
      // User refused Panel install. Let's remove the Panel submodule
      shell_exec("git rm --cached panel");
   } else {
      // User did not enter Y or N
      echo "Please type Y or N.";
   }

   // We really need to verify the installation before printing this message.
   echo "+ Success! Kirby has been installed to " . PATH . "\n";

   // Check for mbstring extension in PHP (required by Kirby)
   if (!extension_loaded('mbstring')) {
      // mbstring not found. Print a warning for the user
      echo "\nWarning: UTF-8 support in PHP not found. (mbstring extension not loaded.) Kirby will have problems with this.";
   }

   exit(0);

}
function install_kirby() {
   // Git clone Kirby Starter Kit to PATH
   echo "+ Working...\n";
   echo "  - Cloning Kirby...\n";
   shell_exec("git clone https://github.com/getkirby/starterkit " . PATH . " --quiet");

   // Change back to PATH
   chdir(PATH);

   // Remove the Kirby origin
   shell_exec("git remote remove origin");
}

function initialize_kirby() {
   // Initialize the Kirby System Folder submodule
   echo "  - Initializing Kirby system folder...\n";
   shell_exec("git submodule --quiet init kirby && git submodule --quiet update kirby");
}

function initialize_toolkit() {
   // Initialize Kirby Toolkit
   echo "  - Initizalizing Kirby toolkit...\n";
   chdir("kirby");
   shell_exec("git submodule --quiet init toolkit && git submodule --quiet update toolkit");
   chdir(PATH);
}
// This function let's us update Kirby using git submodules
function update() {

   precheck();

   if (!file_exists(PATH. "/kirby/kirby.php")) {
      exit("Dedede cannot find a Kirby installation at " . PATH);
   } elseif (!file_exists(PATH . "/.git/config")){
      exit("\nIt seems Dedede did not install Kirby to " . PATH . "\nAlternatively, the Kirby installation wasn't cloned with git.\nDedede can only update Kirby installations installed with Dedede or cloned with git.\n\n");
   }

   // Confirm that we want to update Kirby at PATH
   echo "+ Do you want to update Kirby at " . PATH . "? [Y/N]: ";
   $response = trim(fgets(STDIN));

   // Validate user response
   if ($response == "Y"){
      // User confirmed update. Let's update Kirby!
      doupdate();
   } elseif ($response == "N"){
      // User refused update. Let's exit.
      exit;
   } else {
      // User did not enter Y or N
      echo "Please type Y or N.";
   }
}

// Performs the actual update on Kirby by updating git submodules
function doupdate() {
   echo "+ Working...\n";
   chdir(PATH); // Change to the Kirby project directory
   // Update the Kirby System Folder submodule
   echo "  - Updating Kirby system folder...\n";
   shell_exec("git submodule --quiet update kirby");

   // Update Kirby Toolkit
   echo "  - Updating Kirby toolkit...\n";
   chdir("kirby"); // Change to the Kirby directory
   shell_exec("git submodule --quiet update toolkit");
   chdir(PATH); // Change back to Kirby project directory

   // Update the Panel if it exists
   if (file_exists(PATH . '/panel/index.php')) {
      echo "  - Updating Kirby Panel…\n";
      shell_exec("git submodule --quiet update panel");
   }
   // We really need to validate the status before printing this
   echo "+ Success! Kirby has been updated at " . PATH . "\n";
   exit(0);
}


// Perform various checks before installing or updating
function precheck() {

   // Check if the target PATH exists
   if (file_exists(PATH)) {

      if (COMMAND == "install") {
         // Check if the target PATH is empty
         if (count(scandir(PATH)) > 2) {
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
   echo " + Target empty: ";
   if (count(scandir(PATH)) > 2) {echo "No\n";} else {echo "Yes\n";}
   echo " + Target Kirby: ";
   if (file_exists(PATH. "/kirby/kirby.php")) {echo "Yes\n";} else {echo "No\n";}
   echo " + Target Panel: ";
   if (file_exists(PATH. "/panel/index.php")) {echo "Yes\n";} else {echo "No\n";}
   echo " + Target Git Repo: ";
   if (file_exists(PATH. "/.git/config")) {echo "Yes\n";} else {echo "No\n";}
   echo "\n\nPlease include the above output in your support request.\n";
}

// Print simple help information for Dedede
function help() {
   echo "Dedede is a command line tool for creating and updating Kirby CMS installations.\n\nDedede can install the latest Kirby release to a directory of your choosing by executing `dedede install /path/to/install/kirby`. Dedede will download the latest Kirby release from GitHub and ask if you wish to install the Kirby Panel. By using Dedede, you can remove many of the, otherwise tedious, steps involved in setting up an easily updateable Kirby installation.\n\nDedede can update any Kirby installation that was cloned from GitHub or created using Dedede. Dedede uses git submodules to do this.\n\nDedede is a personal project that may or may not receive new features beyond this core functionality. Dedede was crafted by Corey Edwards (@cedwardsmedia) and is licensed under the MIT License.\n";
   exit(0);
}

// Print usage information
function usage() {
   echo "Usage: dedede [command] /path/to/project\n\n  + Available commands:\n    - install => Dedede will install a fresh copy of Kirby to the specified path.\n      Example: dedede install /home/cedwardsmedia/kirby\n\n    - update => Dedede will update the existing copy of Kirby at the specified path.\n      Example: dedede update /home/cedwardsmedia/kirby-outdated\n\n  + Caveats:\n    - Dedede does not currently handle errors encountered during git processes.\n      (This will be fixed very soon.)\n    - When installing Kirby, the path must either not exist or must be empty.\n    - Dedede can not update a non-git (or non-Dedede installed) copy of Kirby.\n\n  + Notes:\n    - Dedede officially supports Kirby 2.2+. However, it should work for all of 2.x\n";
   exit(0);
}

// Print version information
function version() {
   echo "Dedede v" . VERSION . "\n";
   exit(0);
}
