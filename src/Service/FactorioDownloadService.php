<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Service;

use FactorioItemBrowser\Export\Exception\CommandException;
use FactorioItemBrowser\Export\Process\DownloadProcess;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * The service helping with downloading the Factorio game from official sources.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class FactorioDownloadService
{
    public const VARIANT_FULL = 'full';
    public const VARIANT_HEADLESS = 'headless';

    private const FACTORIO_DOWNLOAD_URLS = [
        self::VARIANT_FULL => 'https://www.factorio.com/get-download/%s/alpha/linux64',
        self::VARIANT_HEADLESS => 'https://www.factorio.com/get-download/%s/headless/linux64',
    ];
    private const LATEST_VERSION_URL = 'https://factorio.com/api/latest-releases';

    private Filesystem $fileSystem;
    private string $factorioApiUsername;
    private string $factorioApiToken;

    public function __construct(
        Filesystem $filesystem,
        string $factorioApiUsername,
        string $factorioApiToken,
    ) {
        $this->fileSystem = $filesystem;
        $this->factorioApiUsername = $factorioApiUsername;
        $this->factorioApiToken = $factorioApiToken;
    }

    /**
     * Creates a process to download an instance of Factorio.
     * @param string $variant
     * @param string $version
     * @param string $destinationFile
     * @return DownloadProcess<string>
     */
    public function createFactorioDownloadProcess(
        string $variant,
        string $version,
        string $destinationFile,
    ): DownloadProcess {
        return new DownloadProcess(
            $this->buildUrl(sprintf(self::FACTORIO_DOWNLOAD_URLS[$variant], $version)),
            $destinationFile,
        );
    }

    /**
     * @param string $archiveFile
     * @param string $destinationDirectory
     * @return Process<string>
     */
    public function createFactorioExtractProcess(string $archiveFile, string $destinationDirectory): Process
    {
        $this->fileSystem->remove($destinationDirectory);
        $this->fileSystem->mkdir($destinationDirectory);

        $process = new Process(['tar', '-xf', $archiveFile, '-C', $destinationDirectory, '--strip', '1']);
        $process->setTimeout(null);
        return $process;
    }

    /**
     * Fetches and returns the latest version of Factorio.
     * @return string
     * @throws CommandException
     */
    public function getLatestVersion(): string
    {
        $response = @file_get_contents($this->buildUrl(self::LATEST_VERSION_URL));
        if ($response === false) {
            throw new CommandException('Failed to fetch latest version of Factorio.');
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            throw new CommandException(sprintf('Invalid response: %s', json_last_error()));
        }

        $version = $data['stable']['alpha'] ?? null;
        if (!$version) {
            throw new CommandException('Unable to read latest version from response.');
        }

        return $version;
    }

    /**
     * @param string $url
     * @param array<string, string> $parameters
     * @return string
     */
    protected function buildUrl(string $url, array $parameters = []): string
    {
        return $url . '?' . http_build_query(array_merge($parameters, [
            'username' => $this->factorioApiUsername,
            'token' => $this->factorioApiToken,
        ]));
    }
}
