<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Process;

use Symfony\Component\Process\Process;

/**
 * The process to download a file from the web.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class DownloadProcess extends Process
{
    /**
     * The full download URL.
     * @var string
     */
    protected $downloadUrl;

    /**
     * The destination of the downloaded file.
     * @var string
     */
    protected $destinationFile;

    /**
     * Initializes the process.
     * @param string $downloadUrl
     * @param string $destinationFile
     */
    public function __construct(string $downloadUrl, string $destinationFile)
    {
        parent::__construct([
            'wget',
            '-o',
            '/dev/null',
            '-O',
            $destinationFile,
            $downloadUrl,
        ]);

        $this->downloadUrl = $downloadUrl;
        $this->destinationFile = $destinationFile;

        $this->setTimeout(null);
    }

    /**
     * Returns the full download URL.
     * @return string
     */
    public function getDownloadUrl(): string
    {
        return $this->downloadUrl;
    }

    /**
     * Returns the destination of the downloaded file.
     * @return string
     */
    public function getDestinationFile(): string
    {
        return $this->destinationFile;
    }
}
