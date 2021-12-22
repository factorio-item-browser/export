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
    public function __construct(
        private readonly string $downloadUrl,
        private readonly string $destinationFile,
    ) {
        parent::__construct([
            'wget',
            '-o',
            '/dev/null',
            '-O',
            $destinationFile,
            $downloadUrl,
        ]);

        $this->setTimeout(null);
    }

    public function getDownloadUrl(): string
    {
        return $this->downloadUrl;
    }

    public function getDestinationFile(): string
    {
        return $this->destinationFile;
    }
}
