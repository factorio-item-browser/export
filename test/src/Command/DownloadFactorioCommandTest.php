<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Command\DownloadFactorioCommand;
use FactorioItemBrowser\Export\Constant\CommandName;
use FactorioItemBrowser\Export\Factorio\FactorioDownloader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The PHPUnit test of the DownloadFactorioCommand class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\DownloadFactorioCommand
 */
class DownloadFactorioCommandTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked factorio downloader.
     * @var FactorioDownloader&MockObject
     */
    protected $factorioDownloader;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->factorioDownloader = $this->createMock(FactorioDownloader::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $command = new DownloadFactorioCommand($this->factorioDownloader);

        $this->assertSame($this->factorioDownloader, $this->extractProperty($command, 'factorioDownloader'));
    }

    /**
     * Tests the configure method.
     * @throws ReflectionException
     * @covers ::configure
     */
    public function testConfigure(): void
    {
        /* @var DownloadFactorioCommand&MockObject $command */
        $command = $this->getMockBuilder(DownloadFactorioCommand::class)
                        ->onlyMethods(['setName', 'setDescription', 'addArgument'])
                        ->setConstructorArgs([$this->factorioDownloader])
                        ->getMock();
        $command->expects($this->once())
                ->method('setName')
                ->with($this->identicalTo(CommandName::DOWNLOAD_FACTORIO));
        $command->expects($this->once())
                ->method('setDescription')
                ->with($this->isType('string'));
        $command->expects($this->once())
                ->method('addArgument')
                ->with(
                    $this->identicalTo('version'),
                    $this->identicalTo(InputArgument::REQUIRED),
                    $this->isType('string')
                );

        $this->invokeMethod($command, 'configure');
    }

    /**
     * Tests the execute method.
     * @throws ReflectionException
     * @covers ::execute
     */
    public function testExecute(): void
    {
        $version = '1.2.3';

        /* @var OutputInterface&MockObject $output */
        $output = $this->createMock(OutputInterface::class);

        /* @var InputInterface&MockObject $input */
        $input = $this->createMock(InputInterface::class);
        $input->expects($this->once())
              ->method('getArgument')
              ->with($this->identicalTo('version'))
              ->willReturn($version);

        $this->factorioDownloader->expects($this->once())
                                 ->method('download')
                                 ->with($this->identicalTo($version));

        $command = new DownloadFactorioCommand($this->factorioDownloader);
        $result = $this->invokeMethod($command, 'execute', $input, $output);

        $this->assertSame(0, $result);
    }
}
