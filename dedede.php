#!/usr/bin/php
<?php

if (phpversion() < "5.3") {
   echo("Kirby CMS requires PHP 5.3 or greater. You are currently running PHP " . phpversion() . ". Please upgrade PHP.\n");
   exit(1);
   }

// Check to see if a command has been issued
if ( array_key_exists ("1", $_SERVER['argv']) ) {
   define(COMMAND, $_SERVER['argv'][1]);
} else {
   usage();
}

// Determine our target path
if ( array_key_exists("2",$_SERVER['argv']) ) {
   $path = $_SERVER['argv'][2];
   if ($path[0] == DIRECTORY_SEPARATOR) {
      define(PATH, $_SERVER['argv'][1]);
   } else {
      define(PATH, getcwd() . "/" . $_SERVER['argv'][1]);
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

   if (!extension_loaded('mbstring')) {
      echo "Warning: UTF-8 support in PHP not found. (mbstring extension not loaded.)";
   }

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
}

function usage() {
   echo "Usage goes here";
   exit;
}
