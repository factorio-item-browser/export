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
    protected FactorioDownloader $factorioDownloader;

    public function __construct(FactorioDownloader $factorioDownloader)
    {
        parent::__construct();

        $this->factorioDownloader = $factorioDownloader;
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setName(CommandName::DOWNLOAD_FACTORIO);
        $this->setDescription('Downloads the Factorio game itself.');

        $this->addArgument('version', InputArgument::REQUIRED, 'The version of Factorio to download.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $version = strval($input->getArgument('version'));

        $this->factorioDownloader->download($version);
        return 0;
    }
}
