<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Factorio;

use FactorioItemBrowser\Export\Entity\ExportCombination;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Parser\ParserManager;
use FactorioItemBrowser\Export\Reducer\ReducerManager;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Service\ExportDataService;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface;

/**
 * The class managing one instance of the Factorio game.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class Instance
{
    /**
     * The export data service.
     * @var ExportDataService
     */
    protected $exportDataService;

    /**
     * The dump extractor.
     * @var DumpExtractor
     */
    protected $dumpExtractor;

    /**
     * The parser manager.
     * @var ParserManager
     */
    protected $parserManager;

    /**
     * The reducer manager.
     * @var ReducerManager
     */
    protected $reducerManager;

    /**
     * The console.
     * @var AdapterInterface
     */
    protected $console;

    /**
     * The options of the instance.
     * @var Options
     */
    protected $options;

    /**
     * The unique index of the instance.
     * @var int
     */
    protected $index;

    /**
     * The directory of the instance.
     * @var string
     */
    protected $directory;

    /**
     * The temporary file to use for the output of Factorio.
     * @var string
     */
    protected $tempFileName;

    /**
     * Whether the instance directory has been set up.
     * @var bool
     */
    protected $isSetUp = false;

    /**
     * The currently running export combination.
     * @var ExportCombination|null
     */
    protected $currentCombination = null;

    /**
     * The process id of the currently running export.
     * @var int
     */
    protected $currentProcessId = 0;

    /**
     * The current attempt in exporting the combination.
     * @var int
     */
    protected $attempt = 0;

    /**
     * Initializes the Factorio instance.
     * @param ExportDataService $exportDataService
     * @param DumpExtractor $dumpExtractor
     * @param ParserManager $parserManager
     * @param ReducerManager $reducerManager
     * @param AdapterInterface $console
     * @param Options $options
     * @param int $index
     */
    public function __construct(
        ExportDataService $exportDataService,
        DumpExtractor $dumpExtractor,
        ParserManager $parserManager,
        ReducerManager $reducerManager,
        AdapterInterface $console,
        Options $options,
        int $index
    ) {
        $this->exportDataService = $exportDataService;
        $this->dumpExtractor = $dumpExtractor;
        $this->parserManager = $parserManager;
        $this->reducerManager = $reducerManager;
        $this->console = $console;
        $this->options = $options;

        $this->directory = $this->options->getInstancesDirectory() . '/instance' . $index;
        $this->index = $index;
        $this->tempFileName = tempnam(sys_get_temp_dir(), 'factorio_');
    }

    /**
     * Finalizes the Factorio instance.
     */
    public function __destruct()
    {
        $this->tearDown();
        if (file_exists($this->tempFileName)) {
            unlink($this->tempFileName);
        }
    }

    /**
     * Sets up the instance files, ready to be executed.
     * @return $this
     */
    protected function setUp()
    {
        if (!$this->isSetUp) {
            if (is_dir($this->directory)) {
                $this->tearDown();
            }

            mkdir($this->directory, 0777);
            mkdir($this->directory . '/mods', 0777);
            mkdir($this->directory . '/bin', 0777);
            mkdir($this->directory . '/bin/x64', 0777);

            $this
                ->copy('bin/x64/factorio')
                ->copy('config-path.cfg')
                ->createSymlink('data');

            $this->isSetUp = true;
        }
        return $this;
    }

    /**
     * Creates a symlink to the specified directory.
     * @param string $directoryOrFile
     * @return $this
     */
    protected function createSymlink(string $directoryOrFile)
    {
        $target = realpath($this->options->getFactorioDirectory() . '/' . $directoryOrFile);
        $link = $this->directory . '/' . $directoryOrFile;

        symlink($target, $link);
        return $this;
    }

    /**
     * Copies a file or directory to the instance.
     * @param string $directoryOrFile
     * @return $this
     */
    protected function copy(string $directoryOrFile)
    {
        $target = realpath($this->options->getFactorioDirectory() . '/' . $directoryOrFile);
        $destination = $this->directory . '/' . $directoryOrFile;
        copy($target, $destination);
        chmod($destination, 0755);
        return $this;
    }

    /**
     * Tears down the instance directory, removing any traces of it.
     * @return $this
     */
    protected function tearDown()
    {
        if ($this->isSetUp) {
            $this->clearDirectory($this->directory);
            rmdir($this->directory);
        }
        return $this;
    }

    /**
     * Clears all files and sub directories from the specified directory.
     * @param string $directory
     * @return $this
     */
    protected function clearDirectory(string $directory)
    {
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
        return $this;
    }

    /**
     * Executes the specified export.
     * @param ExportCombination $combination
     * @return $this
     * @throws ExportException
     */
    public function execute(ExportCombination $combination)
    {
        if ($this->currentProcessId !== 0) {
            throw new ExportException('Tried to start export on currently busy instance #' . $this->index);
        }
        $this->currentCombination = $combination;
        $this->attempt = 1;

        $this
            ->setUp()
            ->prepareForCombination($combination)
            ->executeCombination($this->currentCombination);
        return $this;
    }

    /**
     * Prepares the instance to execute the specified export combination.
     * @param ExportCombination $combination
     * @return $this
     * @throws ExportException
     */
    protected function prepareForCombination(ExportCombination $combination)
    {
        $this->clearDirectory($this->directory . '/mods');
        foreach ($combination->getLoadedModNames() as $modName) {
            $mod = $this->exportDataService->getMod($modName);
            if (!$mod instanceof Mod) {
                throw new ExportException('Missing mod: ' . $modName);
            }

            $this->createSymlink('mods/' . $mod->getFileName());
        }
        $this->createSymlink('mods/Dump_1.0.0');
        return $this;
    }

    /**
     * Executes the specified combination.
     * @param ExportCombination $combination
     * @return $this
     * @throws ExportException
     */
    protected function executeCombination(ExportCombination $combination)
    {
        $this->console->writeLine(
            ' > Exporting combination ' . $combination->getName() . ' on instance #' . $this->index
        );

        $command = $this->directory
            . '/bin/x64/factorio --no-log-rotation --create dump --mod-directory '
            . realpath($this->directory . '/mods');

        $this->console->writeLine('   Command: ' . $command, ColorInterface::GRAY);
        exec(sprintf('%s > %s 2>&1 & echo $!', $command, $this->tempFileName), $processIds);

        $this->currentProcessId = intval(array_pop($processIds));
        return $this;
    }

    /**
     * Checks whether an export combination is currently running.
     * @return bool
     * @throws ExportException
     */
    public function hasRunningCombination(): bool
    {
        $result = false;
        if ($this->currentProcessId > 0) {
            if (file_exists('/proc/' . $this->currentProcessId)) {
                $result = true;
            } else {
                $this->currentProcessId = 0;
                $this->processCombination($this->currentCombination, file_get_contents($this->tempFileName));
                $result = $this->currentProcessId !== 0;
            }
        }
        return $result;
    }

    /**
     * Processes the specified export combination.
     * @param ExportCombination $combination
     * @param string $output
     * @return $this
     * @throws ExportException
     */
    protected function processCombination(ExportCombination $combination, string $output)
    {
        try {
            $this->console->writeLine(
                ' > Processing combination ' . $combination->getName() . ' on instance #' . $this->index
            );
            $exportDump = $this->dumpExtractor->extract($output);
            $this->parserManager->parse($combination, $exportDump);
            $this->reducerManager->addCombination($combination);
        } catch (ExportException $e) {
            $this->console->writeLine(str_pad('', $this->console->getWidth(), '-'), ColorInterface::LIGHT_RED);
            $this->console->writeLine('ExportException: ' . $e->getMessage(), 10);
            $this->console->writeLine(str_pad('', $this->console->getWidth(), '-'), ColorInterface::LIGHT_RED);

            if ($this->attempt < $this->options->getNumberOfAttempts()) {
                $this->console->writeLine(' > Restarting export...');
                ++$this->attempt;
                $this->executeCombination($combination);
            }
        }
        return $this;
    }
}