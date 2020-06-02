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
    private $dataDirectory;
    private $config;
    private $parser;

    public function __construct()
    {
        $this->rootDirectory = dirname(__DIR__) . '/var';
        $this->dataDirectory = dirname(__DIR__, 2);
        $this->parser = new Parser();
        $this->config = $this->parser->parseFile(sprintf("%s/config.yaml", $this->rootDirectory));
        $this->configDirectory = sprintf($this->config['configDir'], $this->dataDirectory);
    }

    public function getTestCases($type = 'none'): array
    {
        return $this->getCase(array_key_exists($type, $this->config['testObjects']) ? $this->config['testObjects'][$type] : '');
    }

    private function getCase(string $directory)
    {
        $directory = sprintf($directory, $this->configDirectory);
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