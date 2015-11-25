#!/usr/bin/php
<?php

// Define values
define(VERSION, "1.0"); // Dedede version
define(MINPHPVER, "5.3"); // Minimum supported PHP version

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

   if (in_array("--version", $_SERVER['argv']) || in_array("-v", $_SERVER['argv'])){
      version(); // Show version information
   } elseif (in_array("--help", $_SERVER['argv']) || in_array("-h", $_SERVER['argv'])){
      help(); // Show help
   } else {
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

   // No command was passed, print usage
   default:
      usage();
      break;
}


// This function is called when the install command is passed
function install() {

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

   // Git clone Kirby Starter Kit to PATH
   echo "+ Working...\n";
   echo "  - Cloning Kirby...\n";
   shell_exec("git clone https://github.com/getkirby/starterkit " . PATH . " --quiet ");
   chdir(PATH);
   shell_exec("git remote remove origin");

   // Initialize the Kirby System Folder submodule
   echo "  - Initializing Kirby system folder...\n";
   shell_exec("git submodule --quiet init kirby && git submodule --quiet update kirby");

   // Initialize Kirby Toolkit
   echo "  - Initizalizing Kirby toolkit...\n";
   chdir("kirby");
   shell_exec("git submodule --quiet init toolkit && git submodule --quiet update toolkit");
   chdir(PATH);

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
   /* echo "Do you want to start the built-in PHP development server? [Y/N]:";

   $response = trim(fgets(STDIN));
   if ($response == "Y"){
      echo "PHP: Development Server running on http://localhost:8000\n";
      shell_exec("php -S localhost:8000");
   } else {
      exit();
   }*/
}

// This function let's us update Kirby using git submodules
function update() {
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
   if (file_exists('panel')) {
      echo "  - Updating Kirby Panel…\n";
      shell_exec("git submodule --quiet update panel");
   }
   // We really need to validate the status before printing this
   echo "+ Success! Kirby has been updated at " . PATH . "\n";
   exit(0);
}

// Print simple help information for Dedede
function help() {
   echo "Dedede is a command line tool for creating and updating Kirby CMS installations.\n\nDedede can install the latest Kirby release to a directory of your choosing by executing `dedede install /path/to/install/kirby`. Dedede will download the latest Kirby release from Github and ask if you wish to install the Kirby Panel. By using Dedede, you can remove many of the, otherwise tedious, steps involved in setting up an easily updateable Kirby installation.\n\nDedede can update any Kirby installation that was cloned from Github or created using Dedede. Dedede uses git submodules to do this.\n\nDedede is a personal project that may or may not receive new features beyond this core functionality. Dedede was crafted by Corey Edwards (@cedwardsmedia) and is licensed under the MIT License.\n";
   exit(0);
}

// Print usage information
function usage() {
   echo "Usage: dedede [command] /path/to/project\n\n  + Available commands:\n    - install => Dedede will install a fresh copy of Kirby to the specified path.\n      Example: dedede install /home/cedwardsmedia/kirby\n\n    - update => Dedede will update the existing copy of Kirby at the specified path.\n      Example: dedede update /home/cedwardsmedia/kirby-outdated\n\n  + Caveats:\n    - Dedede does not currently understand relative paths (./ ../ ~/ etc.)\n    - When installing Kirby, the path must either not exist or must be empty.\n    - Dedede can not update a non-git (or non-Dedede installed) copy of Kirby.\n\n  + Notes:\n    - Dedede officially supports Kirby 2.2+. However, it should work for all of 2.x\n";
   exit(0);
}

// Print version information
function version() {
   echo "Dedede v" . VERSION . "\n";
   exit(0);
}
