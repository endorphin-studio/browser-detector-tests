<?php
/**
 * @author Serhii Nekhaienko <sergey.nekhaenko@gmail.com>
 * @license GPL
 * @copyright Serhii Nekhaienko &copy 2018
 * @version 4.0.0
 * @project endorphin-studio/browser-detector
 */

namespace EndorphinStudio\Tests;

use Symfony\Component\Yaml\Parser;

class YamlReader
{
    private $rootDirectory;
    private $configDirectory;
    private $vendorDirectory;
    private $dataDirectory;
    private $config;
    private $parser;
    private $directories = [];

    public function __construct()
    {
        $this->rootDirectory = dirname(__DIR__) . '/var';
        $this->vendorDirectory = dirname(__DIR__, 3);
        $this->parser = new Parser();
        $this->directories = [
            '{root}' => $this->rootDirectory,
            '{vendor}' => $this->vendorDirectory
        ];
        $this->config = $this->parser->parseFile(strtr("{root}/config.yaml", $this->directories));
        $this->dataDirectory = strtr($this->config['dataRepo'], $this->directories);
        $this->directories['{data}'] = $this->dataDirectory;
        $this->configDirectory = strtr($this->config['configDir'], $this->directories);
        $this->directories['{config}'] = $this->configDirectory;
    }

    public function getTestCases($type = 'none'): array
    {
        return $this->getCase(array_key_exists($type, $this->config['testObjects']) ? $this->config['testObjects'][$type] : '');
    }

    private function getCase(string $directory)
    {
        $directory = strtr($directory, $this->directories);
        return ['cases' => $this->getConfig($directory)];
    }

    public function getConfig(string $directory): array
    {
        $config = [];
        $files = $this->getFileNames($directory);
        foreach ($files as $fileName => $filePath) {
            $fileConfig[$fileName] = $this->parser->parseFile($filePath);
            $fileConfig[$fileName]['fileName'] = $filePath;
            $config = \array_merge($config, $fileConfig);
        }
        return $config;
    }


    protected function getFileNames(string $directory = 'default'): array
    {
        $directoryIterator = $this->getDirectoryIterator($directory);
        $files = [];
        foreach ($directoryIterator as $file) {
            if ($file->isFile()) {
                $files[$file->getBasename('.yaml')] = $file->getRealPath();
            }
        }
        return $files;
    }

    private function getDirectoryIterator(string $directory): \DirectoryIterator
    {
        return new \DirectoryIterator(str_replace('//', '/', $directory));
    }
}