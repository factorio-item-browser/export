<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Factorio;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Process\Process;

/**
 * The instance of Factorio being run to get the dump data.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class Instance
{
    /**
     * The dump extractor.
     * @var DumpExtractor
     */
    protected $dumpExtractor;

    /**
     * The mod registry.
     * @var ModRegistry
     */
    protected $modRegistry;

    /**
     * The directory containing the actual Factorio game.
     * @var string
     */
    protected $factorioDirectory;

    /**
     * The directory of the instance.
     * @var string|null
     */
    protected $instanceDirectory;

    /**
     * Initializes the instance.
     * @param DumpExtractor $dumpExtractor
     * @param ModRegistry $modRegistry
     * @param string $factorioDirectory
     */
    public function __construct(DumpExtractor $dumpExtractor, ModRegistry $modRegistry, string $factorioDirectory)
    {
        $this->dumpExtractor = $dumpExtractor;
        $this->modRegistry = $modRegistry;
        $this->factorioDirectory = $factorioDirectory;
    }

    /**
     * Cleans up the instance.
     */
    public function __destruct()
    {
        if ($this->instanceDirectory !== null) {
            $this->removeDirectory($this->instanceDirectory);
        }
    }

    /**
     * Runs the combination in a Factorio instance.
     * @param Combination $combination
     * @return DataContainer
     * @throws ExportException
     */
    public function run(Combination $combination): DataContainer
    {
        $this->setUp($combination->calculateHash());
        $this->setUpMods($combination);
        $output = $this->execute();
        return $this->dumpExtractor->extract($output);
    }

    /**
     * Sets up the instance.
     * @param string $combinationHash
     */
    protected function setUp(string $combinationHash): void
    {
        $this->instanceDirectory = $this->factorioDirectory . '/instances/' . $combinationHash;
        $this->removeDirectory($this->instanceDirectory);
        $this->createDirectories($this->instanceDirectory);

        $this->copy('bin/x64/factorio');
        $this->copy('config-path.cfg');
        $this->createSymlink('data');
    }

    /**
     * Sets up the mods to use for the combination.
     * @param Combination $combination
     * @throws ExportException
     */
    protected function setUpMods(Combination $combination): void
    {
        foreach ($combination->getLoadedModNames() as $modName) {
            $mod = $this->modRegistry->get($modName);
            if (!$mod instanceof Mod) {
                throw new ExportException('Mod not known: ' . $modName);
            }

            $this->createSymlink('mods/' . $mod->getFileName());
        }
        $this->createSymlink('mods/Dump_1.0.0');
    }

    /**
     * Executes the Factorio instance.
     * @return string
     */
    protected function execute(): string
    {
        $commandLine = implode(' ', [
            $this->instanceDirectory . '/bin/x64/factorio',
            '--no-log-rotation',
            '--create dump',
            '--mod-directory ' . realpath($this->instanceDirectory . '/mods')
        ]);

        $process = new Process($commandLine);
        $process->start();
        $process->wait();

        return $process->getOutput();
    }

    /**
     * Removes the specified directory if it exists.
     * @param string $directory
     */
    protected function removeDirectory(string $directory): void
    {
        if (is_dir($directory)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($files as $file) {
                /* @var \SplFileInfo $file */
                if ($file->isDir() && !$file->isLink()) {
                    rmdir($file->getPathname());
                } else {
                    unlink($file->getPathname());
                }
            }
            rmdir($directory);
        }
    }

    /**
     * Creates the directories required for the instance to run.
     * @param string $baseDirectory
     */
    protected function createDirectories(string $baseDirectory): void
    {
        mkdir($baseDirectory . '/mods', 0777, true);
        mkdir($baseDirectory . '/bin/x64', 0777, true);
    }

    /**
     * Copies a file or directory to the instance.
     * @param string $directoryOrFile
     */
    protected function copy(string $directoryOrFile): void
    {
        $target = (string) realpath($this->factorioDirectory . '/' . $directoryOrFile);
        $destination = $this->instanceDirectory . '/' . $directoryOrFile;
        copy($target, $destination);
        chmod($destination, 0755);
    }

    /**
     * Creates a symlink to the specified directory.
     * @param string $directoryOrFile
     */
    protected function createSymlink(string $directoryOrFile): void
    {
        $target = (string) realpath($this->factorioDirectory . '/' . $directoryOrFile);
        $link = $this->instanceDirectory . '/' . $directoryOrFile;

        symlink($target, $link);
    }
}
