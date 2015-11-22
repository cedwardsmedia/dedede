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
if ( array_key_exists("2",$_SERVER['argv']) ) {
   $path = $_SERVER['argv'][2];

   if ($path[0] == DIRECTORY_SEPARATOR) {
      define(PATH, $_SERVER['argv'][2]);
   } elseif (substr($path, 0, 2) == "./") {
      define(PATH, getcwd() . "/" . str_replace("./", "", $path));
   } elseif ($path == ".." || $path == "../" ) {
      echo "Cannot install Kirby to parent directory. Target must be empty or not exist.\n";
      exit(1);
   } elseif (substr($path, 0, 2) == ".." && (strlen($path) > 2)) {
      define(PATH, dirname(getcwd()) . "/" . str_replace("../", "", $_SERVER['argv'][2]));
   } else {
      define(PATH, getcwd() . "/" . $_SERVER['argv'][2]);
   }
} else {
   // Get the current working directory
   define(PATH, getcwd());
}

// Process command
switch (COMMAND) {
   // Let's install Kirby!
   case 'install':
      install();
      break;

   case 'update':
   // Let's update Kirby!
      update();
      break;

   default:
      # No command was passed, print usage
      usage();
      break;
}


// This function is called when the install command is passed
function install() {
   echo "I will download Kirby to " . PATH ."\nIs that OK? [Y/N]: ";
   $response = trim(fgets(STDIN));

   if ($response == "Y"){
      doinstall();
   } elseif ($response == "N"){
      exit;
   } else {
      echo "Please type Y or N.\n";
      install();
   }
}

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
      echo "  - Initializing Kirby Panel...\n";
      shell_exec("git submodule --quiet init panel && git submodule --quiet update panel");
   } elseif ($response == "N"){
      shell_exec("git rm --cached panel");
   } else {
      echo "Please type Y or N.";
   }

   echo "+ Success! Kirby has been installed to " . PATH . "\n";

   // Check for mbstring extension in PHP (required by Kirby)
   if (!extension_loaded('mbstring')) {
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
function update() {
   // Ask if we want to keep the panel
   echo "+ Do you want to update Kirby at " . PATH . "? [Y/N]: ";
   $response = trim(fgets(STDIN));

   if ($response == "Y"){
      doupdate();
   } elseif ($response == "N"){
      exit;
   } else {
      echo "Please type Y or N.";
   }
}
function doupdate() {
   echo "+ Working...\n";
   chdir(PATH);
   // Update the Kirby System Folder submodule
   echo "  - Updating Kirby system folder...\n";
   shell_exec("git submodule --quiet update kirby");

   // Update Kirby Toolkit
   echo "  - Updating Kirby toolkit...\n";
   chdir("kirby");
   shell_exec("git submodule --quiet update toolkit");
   chdir(PATH);

   if (file_exists('panel')) {
      echo "  - Updating Kirby Panel…\n";
      shell_exec("git submodule --quiet update panel");
   }
   echo "+ Success! Kirby has been updated at " . PATH . "\n";
   exit(0);
}

function help() {
   echo "Dedede is a command line tool for creating and updating Kirby CMS installations.\n\nDedede can install the latest Kirby release to a directory of your choosing by executing `dedede install /path/to/install/kirby`. Dedede will download the latest Kirby release from Github and ask if you wish to install the Kirby Panel. By using Dedede, you can remove many of the, otherwise tedious, steps involved in setting up an easily updateable Kirby installation.\n\nDedede can update any Kirby installation that was cloned from Github or created using Dedede. Dedede uses git submodules to do this.\n\nDedede is a personal project that may or may not receive new features beyond this core functionality. Dedede was crafted by Corey Edwards (@cedwardsmedia) and is licensed under the MIT License.\n";
   exit(0);
}
function usage() {
   echo "Usage: dedede [command] /path/to/project\n\n  + Available commands:\n    - install => Dedede will install a fresh copy of Kirby to the specified path.\n      Example: dedede install /home/cedwardsmedia/kirby\n\n    - update => Dedede will update the existing copy of Kirby at the specified path.\n      Example: dedede update /home/cedwardsmedia/kirby-outdated\n\n  + Caveats:\n    - Dedede does not currently understand relative paths (./ ../ ~/ etc.)\n    - When installing Kirby, the path must either not exist or must be empty.\n    - Dedede can not update a non-git (or non-Dedede installed) copy of Kirby.\n\n  + Notes:\n    - Dedede officially supports Kirby 2.2+. However, it should work for all of 2.x\n";
   exit(0);
}
function version() {
   echo "Dedede v" . VERSION . "\n";
   exit(0);
}
