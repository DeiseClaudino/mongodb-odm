<?php

declare(strict_types=1);

namespace Doctrine\ODM\MongoDB\Tools\Console\Command\Schema;

use BadMethodCallException;
use Doctrine\ODM\MongoDB\SchemaManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use function is_string;
use function sprintf;

class UpdateCommand extends AbstractCommand
{
    /** @var int|null */
    private $timeout;

    protected function configure()
    {
        $this
            ->setName('odm:schema:update')
            ->addOption('class', 'c', InputOption::VALUE_OPTIONAL, 'Document class to process (default: all classes)')
            ->addOption('timeout', 't', InputOption::VALUE_OPTIONAL, 'Timeout (ms) for acknowledged index creation')
            ->setDescription('Update indexes for your documents');
    }

    /**
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $class         = $input->getOption('class');
        $timeout       = $input->getOption('timeout');
        $this->timeout = is_string($timeout) ? (int) $timeout : null;

        $sm        = $this->getSchemaManager();
        $isErrored = false;

        try {
            if (is_string($class)) {
                $this->processDocumentIndex($sm, $class);
                $output->writeln(sprintf('Updated <comment>index(es)</comment> for <info>%s</info>', $class));
            } else {
                $this->processIndex($sm);
                $output->writeln('Updated <comment>indexes</comment> for <info>all classes</info>');
            }
        } catch (Throwable $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            $isErrored = true;
        }

        return $isErrored ? 255 : 0;
    }

    protected function processDocumentIndex(SchemaManager $sm, string $document)
    {
        $sm->updateDocumentIndexes($document, $this->timeout);
    }

    protected function processIndex(SchemaManager $sm)
    {
        $sm->updateIndexes($this->timeout);
    }

    /**
     * @throws BadMethodCallException
     */
    protected function processDocumentCollection(SchemaManager $sm, string $document)
    {
        throw new BadMethodCallException('Cannot update a document collection');
    }

    /**
     * @throws BadMethodCallException
     */
    protected function processCollection(SchemaManager $sm)
    {
        throw new BadMethodCallException('Cannot update a collection');
    }

    /**
     * @throws BadMethodCallException
     */
    protected function processDocumentDb(SchemaManager $sm, string $document)
    {
        throw new BadMethodCallException('Cannot update a document database');
    }

    /**
     * @throws BadMethodCallException
     */
    protected function processDb(SchemaManager $sm)
    {
        throw new BadMethodCallException('Cannot update a database');
    }
}
