<?php

namespace Utils\StaticAnalyse\ErrorFormatter;

use PHPStan\Command\ErrorFormatter\ErrorFormatter;
use PHPStan\Command\AnalysisResult;
use PHPStan\Command\ErrorFormatter\TableErrorFormatter;
use PHPStan\Command\Output;

final class GithubErrorFormatter implements ErrorFormatter
{
    public function __construct(private TableErrorFormatter $tableErrorFormatter)
    {
    }

    public function formatErrors(AnalysisResult $analysisResult, Output $output) : int
    {
        if (!getenv('GITHUB_SERVER_URL')) {
            return $this->tableErrorFormatter->formatErrors($analysisResult, $output);
        }

        $githubRepository = sprintf(
            '%s/%s',
            getenv('GITHUB_SERVER_URL'),
            getenv('GITHUB_REPOSITORY')
        );
        $pathArr = explode('/', $githubRepository);
        $projectName = $pathArr[array_key_last($pathArr)];
        $actionDir = sprintf('/home/runner/work/%s/%s/', $projectName, $projectName);
        $branch = str_replace('refs/heads/', '', getenv('GITHUB_HEAD_REF'));

        foreach ($analysisResult->getFileSpecificErrors() as $fileSpecificError) {
            $file = $fileSpecificError->getFile();
            $line = $fileSpecificError->getLine();
            $message = $fileSpecificError->getMessage();
            $errorMessage = sprintf(
                "%s/blob/%s%s#L%d - %s",
                $githubRepository,
                $branch,
                str_starts_with($file, $actionDir) ? str_replace($actionDir, '/', $file) : $file,
                $line,
                $message
            );
            // newlines need to be encoded
            // see https://github.com/actions/starter-workflows/issues/68#issuecomment-581479448
            $errorMessage = str_replace("\n", '%0A', $errorMessage);
            $line = sprintf('::error ::%s', $errorMessage);
            $output->writeRaw($line);
            $output->writeLineFormatted('');
        }
        foreach ($analysisResult->getNotFileSpecificErrors() as $errorMessage) {
            // newlines need to be encoded
            // see https://github.com/actions/starter-workflows/issues/68#issuecomment-581479448
            $errorMessage = str_replace("\n", '%0A', $errorMessage);
            $line = sprintf('::error ::%s', $errorMessage);
            $output->writeRaw($line);
            $output->writeLineFormatted('');
        }
        foreach ($analysisResult->getWarnings() as $warning) {
            // newlines need to be encoded
            // see https://github.com/actions/starter-workflows/issues/68#issuecomment-581479448
            $warning = str_replace("\n", '%0A', $warning);
            $line = sprintf('::warning ::%s', $warning);
            $output->writeRaw($line);
            $output->writeLineFormatted('');
        }
        return $analysisResult->hasErrors() ? 1 : 0;
    }
}
