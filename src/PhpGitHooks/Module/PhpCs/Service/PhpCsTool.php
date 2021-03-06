<?php

namespace PhpGitHooks\Module\PhpCs\Service;

use PhpGitHooks\Module\Git\Contract\Response\BadJobLogoResponse;
use PhpGitHooks\Module\Git\Service\PreCommitOutputWriter;
use PhpGitHooks\Module\PhpCs\Contract\Exception\PhpCsViolationException;
use PhpGitHooks\Module\PhpCs\Model\PhpCsToolProcessorInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PhpCsTool
{
    const EXECUTE_MESSAGE = 'Checking code style with PHPCS';
    /**
     * @var OutputInterface
     */
    private $output;
    /**
     * @var PhpCsToolProcessorInterface
     */
    private $phpCsToolProcessor;

    /**
     * PhpCsTool constructor.
     *
     * @param OutputInterface             $output
     * @param PhpCsToolProcessorInterface $phpCsToolProcessor
     */
    public function __construct(OutputInterface $output, PhpCsToolProcessorInterface $phpCsToolProcessor)
    {
        $this->output = $output;
        $this->phpCsToolProcessor = $phpCsToolProcessor;
    }

    /**
     * @param array  $files
     * @param string $standard
     * @param string $errorMessage
     * @param string $ignore
     *
     * @throws PhpCsViolationException
     */
    public function execute(array $files, $standard, $errorMessage, $ignore)
    {
        $outputMessage = new PreCommitOutputWriter(self::EXECUTE_MESSAGE);
        $this->output->write($outputMessage->getMessage());

        $errors = [];
        foreach ($files as $file) {
            $errors[] = $this->phpCsToolProcessor->process($file, $standard, $ignore);
        }

        $errors = array_filter($errors);

        if (!empty($errors)) {
            $this->output->writeln($outputMessage->getFailMessage());
            $this->output->writeln($outputMessage->setError(implode('', $errors)));
            $this->output->writeln(BadJobLogoResponse::paint($errorMessage));
            throw new PhpCsViolationException();
        }
        $this->output->writeln($outputMessage->getSuccessfulMessage());
    }
}
