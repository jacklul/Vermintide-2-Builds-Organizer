<?php

$outputDir = __DIR__ . '/dist/';
$tmpDir = __DIR__ . '/tmp/';
$projectName = preg_replace("/[^A-Za-z0-9]/", '-', basename(__DIR__));

$downloads = [
    'https://github.com/cztomczak/phpdesktop/releases/download/chrome-v57.0-rc/phpdesktop-chrome-57.0-rc-php-7.1.3.zip',
];

#------------------------------------------------------------------------------#

function rrmdir($dir)
{
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $object) && !is_link($dir . "/" . $object))
                    rrmdir($dir . DIRECTORY_SEPARATOR . $object);
                else
                    unlink($dir . DIRECTORY_SEPARATOR . $object);
            }
        }
        rmdir($dir);
    }
}

function recurseCopy(string $sourceDirectory, string $destinationDirectory, string $childFolder = '') {
    $directory = opendir($sourceDirectory);

    if (is_dir($destinationDirectory) === false) {
        mkdir($destinationDirectory);
    }

    if ($childFolder !== '') {
        if (is_dir("$destinationDirectory/$childFolder") === false) {
            mkdir("$destinationDirectory/$childFolder");
        }

        while (($file = readdir($directory)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            if (is_dir("$sourceDirectory/$file") === true) {
                recurseCopy("$sourceDirectory/$file", "$destinationDirectory/$childFolder/$file");
            } else {
                copy("$sourceDirectory/$file", "$destinationDirectory/$childFolder/$file");
            }
        }

        closedir($directory);

        return;
    }

    while (($file = readdir($directory)) !== false) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        if (is_dir("$sourceDirectory/$file") === true) {
            recurseCopy("$sourceDirectory/$file", "$destinationDirectory/$file");
        } else {
            copy("$sourceDirectory/$file", "$destinationDirectory/$file");
        }
    }

    closedir($directory);
}

function fileRenameMoveCopyLog($file1, $files2, $operation) {
    $operations = [
        'copy' => 'Copying',
        'move' => 'Moving',
        'rename' => 'Renaming',
    ];

    echo $operations[strtolower($operation)] . ': ' . str_replace(__DIR__ . '/', '', $file1) . ' => ' . str_replace(__DIR__ . '/', '', $files2) . PHP_EOL;
}

#------------------------------------------------------------------------------#

if (!is_dir($outputDir)) {
    mkdir($outputDir);
}

if (!is_dir($tmpDir)) {
    mkdir($tmpDir);
}

$options = [
    "http" => [
        "user_agent" => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.77 Safari/537.36'
    ]
];
$context = stream_context_create($options);

// Get latest PHP 7.4
echo 'Getting latest PHP 7.4 download link...' . PHP_EOL;

$phpReleases = file_get_contents('https://windows.php.net/download/', false, $context);

preg_match_all('/(\/downloads\/releases\/php-7\.4\.\d+-nts-Win32-vc15-x64\.zip)/', $phpReleases, $matches);

if (isset($matches[1][0])) {
    $downloads[] = 'https://windows.php.net'  . $matches[1][0];
} else {
    exit('Failed to find latest PHP 7.4 download link!' . PHP_EOL);
}

// Get latest NodeJS 16
echo 'Getting latest NodeJS 16 download link...' . PHP_EOL;

$nodeReleases = file_get_contents('https://nodejs.org/en/download/', false, $context);

preg_match_all('/(\/dist\/v16\.\d+\.0\/node-v16\.\d+\.\d+-win-x64\.zip)/', $nodeReleases, $matches);

if (isset($matches[1][0])) {
    $downloads[] = 'https://nodejs.org'  . $matches[1][0];
} else {
    exit('Failed to find latest NodeJS 16 download link!' . PHP_EOL);
}

$phpDesktopDir = '';
$phpDir = '';
$nodeDir = '';

// Download and extract archives
foreach ($downloads as $download) {
    if (!file_exists($tmpDir . basename($download))) {
        echo 'Downloading "' . $download . '"' . PHP_EOL;

        if ($file = file_get_contents($download, false, $context)) {
            file_put_contents($tmpDir . basename($download), $file);
        }
    }

    $basenameWithoutExtension = explode('.', basename($download));
    unset($basenameWithoutExtension[count($basenameWithoutExtension) - 1]);
    $basenameWithoutExtension = implode('.', $basenameWithoutExtension);

    if (!is_dir($tmpDir . $basenameWithoutExtension)) {
        $zip = new ZipArchive;
        if ($zip->open($tmpDir . basename($download))) {
            $zip->extractTo($tmpDir . $basenameWithoutExtension);
            $zip->close();

            echo 'Extracted "' . basename($download) . '" to "' . $tmpDir . $basenameWithoutExtension . '"' . PHP_EOL;
        } else {
            echo 'Failed to open "' . basename($download) . '"' . PHP_EOL;
        }
    }

    if (strpos($basenameWithoutExtension, 'phpdesktop-') !== false) {
        $phpDesktopDir = $basenameWithoutExtension;
    } elseif (strpos($basenameWithoutExtension, 'php-') !== false) {
        $phpDir = $basenameWithoutExtension;
    } elseif (strpos($basenameWithoutExtension, 'node-') !== false) {
        $nodeDir = $basenameWithoutExtension;
    }
}

if (is_dir($outputDir . $projectName)) {
    echo 'Cleaning up "' . str_replace(__DIR__ . '/', '', $outputDir . $projectName) . '"...' . PHP_EOL;

    rrmdir($outputDir . $projectName);
    mkdir($outputDir . $projectName);
}

echo 'Copying dependencies...' . PHP_EOL;

if (!empty($phpDesktopDir) && !file_exists($outputDir . $projectName . '/phpdesktop-chrome.exe')) {
    if (is_dir($tmpDir . $phpDesktopDir . '/' . $phpDesktopDir)) {
        recurseCopy($tmpDir . $phpDesktopDir . '/' . $phpDesktopDir, $outputDir . $projectName);

        fileRenameMoveCopyLog($tmpDir . $phpDesktopDir . '/' . $phpDesktopDir, $outputDir . $projectName, 'copy');
    }

    if (is_dir($outputDir . $projectName . '/php/')) {
        rrmdir($outputDir . $projectName . '/php/');
        rrmdir($outputDir . $projectName . '/www/');
    }
}

if (!empty($phpDir) && !file_exists($outputDir . $projectName . '/php/php.exe')) {
    if (is_dir($tmpDir . $phpDir)) {
        recurseCopy($tmpDir . $phpDir, $outputDir . $projectName . '/php');

        fileRenameMoveCopyLog($tmpDir . $phpDir, $outputDir . $projectName . '/php', 'copy');
    }
}

if (!empty($nodeDir) && !file_exists($outputDir . $projectName . '/node/node.exe')) {
    if (is_dir($tmpDir . $nodeDir . '/' . $nodeDir)) {
        recurseCopy($tmpDir . $nodeDir . '/' . $nodeDir, $outputDir . $projectName . '/node');

        fileRenameMoveCopyLog($tmpDir . $nodeDir . '/' . $nodeDir, $outputDir . $projectName . '/node', 'copy');
    }
}

echo 'Renaming files...' . PHP_EOL;

$filesToRename = [
    'license.txt' => 'phpdesktop-license.txt',
];

foreach ($filesToRename as $fileToRename => $fileToRenameTo) {
    if (file_exists($outputDir . $projectName . '/' . $fileToRename)) {
        rename($outputDir . $projectName . '/' . $fileToRename, $outputDir . $projectName . '/' . $fileToRenameTo);

        fileRenameMoveCopyLog($outputDir . $projectName . '/' . $fileToRename, $outputDir . $projectName . '/' . $fileToRenameTo, 'rename');
    }
}

echo 'Copying project files...' . PHP_EOL;

$filesToCopy = [
    __DIR__ . '/LICENSE',
    __DIR__ . '/settings.json',
    __DIR__ . '/puppeteer/package.json',
    __DIR__ . '/puppeteer/load_page.js',
    __DIR__ . '/puppeteer/load_page_external.js',
    __DIR__ . '/www/',
];

foreach ($filesToCopy as $fileToCopy) {
    $destination = str_replace(__DIR__, $outputDir . $projectName, $fileToCopy);
    $destinationDir = dirname($destination);

    if (is_dir($fileToCopy)) {
        recurseCopy($fileToCopy, $destination);
    } else {
        if (!is_dir($destinationDir)) {
            mkdir($destinationDir);
        }

        copy($fileToCopy, $destination);
    }

    fileRenameMoveCopyLog($fileToCopy, $destination, 'copy');
}

echo 'Installing puppeteer (this can take a while)...' . PHP_EOL;

if (isset($argv[1]) && strtolower($argv[1]) === 'no-chrome') {
    $data = file_get_contents($outputDir . $projectName . '/puppeteer/package.json');
    $data = str_replace('"puppeteer"', '"puppeteer-core"', $data);
    file_put_contents($outputDir . $projectName . '/puppeteer/package.json', $data);
}

chdir($outputDir . $projectName . '/puppeteer');
passthru('cmd /c ""' . $outputDir . $projectName . '/node/npm" install"', $return);

if ($return !== 0) {
    exit('Failed to install puppeteer!' . PHP_EOL);
}
