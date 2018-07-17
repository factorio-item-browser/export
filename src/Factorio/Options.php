<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Factorio;

/**
 * The options used by the instances.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class Options
{
    /**
     * The number of attempts to try executing a combination.
     * @var int
     */
    protected $numberOfAttempts = 1;

    /**
     * The directory of the Factorio game itself.
     * @var string
     */
    protected $factorioDirectory = '';

    /**
     * The directory of the instances.
     * @var string
     */
    protected $instancesDirectory = '';

    /**
     * Sets the number of attempts to try executing a combination.
     * @param int $numberOfAttempts
     * @return $this
     */
    public function setNumberOfAttempts(int $numberOfAttempts)
    {
        $this->numberOfAttempts = $numberOfAttempts;
        return $this;
    }

    /**
     * Returns the number of attempts to try executing a combination.
     * @return int
     */
    public function getNumberOfAttempts(): int
    {
        return $this->numberOfAttempts;
    }

    /**
     * Sets the directory of the Factorio game itself.
     * @param string $factorioDirectory
     * @return $this
     */
    public function setFactorioDirectory(string $factorioDirectory)
    {
        $this->factorioDirectory = $factorioDirectory;
        return $this;
    }

    /**
     * Returns the directory of the Factorio game itself.
     * @return string
     */
    public function getFactorioDirectory(): string
    {
        return $this->factorioDirectory;
    }

    /**
     * Sets the directory of the instances.
     * @param string $instancesDirectory
     * @return $this
     */
    public function setInstancesDirectory(string $instancesDirectory)
    {
        $this->instancesDirectory = $instancesDirectory;
        return $this;
    }

    /**
     * Returns the directory of the instances.
     * @return string
     */
    public function getInstancesDirectory(): string
    {
        return $this->instancesDirectory;
    }
}
