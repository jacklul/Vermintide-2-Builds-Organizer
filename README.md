# Vermintide 2 Builds Organizer

This tool can help your organize your build and equipment library.
For screenshots [go here](https://imgur.com/a/bGT1LNV).

**Warning: This project is a spaghetti code, the whole build importer probably could've been better implemented than using NodeJS + Puppeteer.**

## Installation

Download release zip from [Releases](https://github.com/jacklul/Vermintide-2-Builds-Organizer/releases) tab and extract it to directory of your choice, run the tool using `phpdesktop-chrome.exe` executable.

## Manual installation

### Using build script

- [Download this repository contents](https://github.com/jacklul/Vermintide-2-Builds-Organizer/archive/refs/heads/master.zip) and extract it to directory of your choice
- Go to [windows.php.net](https://windows.php.net/download/) and download latest **PHP 7.4** - **VC15 x64 Non Thread Safe** variant ZIP archive
- Extract the archive to `php` directory inside unpacked tool directory
- Open command line in the extracted tool directory (execute `cd "C:/Extracted-Tool-Dir-Here/"` to change directory) and:
    - if you do not have Chrome installed execute this command: `php/php build.php`
    - if you already have Chrome installed execute this comman: `php/php build.php no-chrome`
- This should automatically do all the steps from manual installation and output the runnable tool in `dist/` directory

### Doing everything manually

- [Download this repository contents](https://github.com/jacklul/Vermintide-2-Builds-Organizer/archive/refs/heads/master.zip) and extract it to directory of your choice
- Go to [cztomczak/phpdesktop/releases](https://github.com/cztomczak/phpdesktop/releases/tag/chrome-v57.0-rc) and download attached `phpdesktop-chrome-57.0-rc-php-7.1.3.zip`
- Open downloaded zip unpack `phpdesktop-chrome-57.0-rc-php-7.1.3` contents to some temporary directory
- Move everything except `settings.json` file, `php` and `www` directories to the unpacked tool directory
- Go to [windows.php.net](https://windows.php.net/download/) and download **PHP 7.4** - **Non Thread Safe** variant
- Extract zip contents to `php` directory
- Go to [nodejs.org](https://nodejs.org/en/download/) and download **Node.js 16** - **Windows Binary (.zip)** variant, 64bit recommended
- Open downloaded zip, navigate to `node-v*.*.*-win-x*` folder, then extract contents to `node` directory
- If you do have Chrome ąlready installed (`C:/Program Files (x86)/Google/Chrome/Application/chrome.exe`) then open `puppeteer/package.json` file and replace `puppeteer` with `puppeteer-core`
- Open command line, `cd` into puppeteer (`cd puppeteer`) directory and then run `../node/npm install` and wait...
- Running `phpdesktop-chrome.exe` should now open the tool
