<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command;

use FactorioItemBrowser\Export\Constant\CommandName;
use FactorioItemBrowser\Export\Factorio\FactorioDownloader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The process for downloading the Factorio game itself.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class DownloadFactorioCommand extends Command
{
    /**
     * The factorio downloader.
     * @var FactorioDownloader
     */
    protected $factorioDownloader;

    /**
     * DownloadFactorioCommand constructor.
     * @param FactorioDownloader $factorioDownloader
     */
    public function __construct(FactorioDownloader $factorioDownloader)
    {
        parent::__construct();

        $this->factorioDownloader = $factorioDownloader;
    }

    /**
     * Configures the command.
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setName(CommandName::DOWNLOAD_FACTORIO);
        $this->setDescription('Downloads the Factorio game itself.');

        $this->addArgument('version', InputArgument::REQUIRED, 'The version of Factorio to download.');
    }

    /**
     * Executes the command.
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $version = strval($input->getArgument('version'));

        $this->factorioDownloader->download($version);
        return 0;
    }
}
