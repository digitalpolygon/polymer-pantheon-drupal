<?php

namespace DigitalPolygon\Polymer\polymer_pantheon_drupal\Plugin\Hooks\Hosting;

use Consolidation\AnnotatedCommand\AnnotatedCommand;
use Consolidation\AnnotatedCommand\AnnotationData;
use Consolidation\AnnotatedCommand\Attributes\Hook;
use Consolidation\AnnotatedCommand\CommandData;
use Consolidation\AnnotatedCommand\Hooks\HookManager;
use DigitalPolygon\Polymer\Core\Robo\Commands\Artifact\DeployCommand;
use DigitalPolygon\Polymer\Core\Robo\Tasks\TaskBase;
use DigitalPolygon\Polymer\polymer_pantheon_drupal\Hosting\Pantheon\PantheonRepository;

class PantheonArtifactHook extends TaskBase
{
    #[Hook(type: HookManager::OPTION_HOOK, target: DeployCommand::ARTIFACT_DEPLOY_COMMAND)]
    public function addPantheonOptions(AnnotatedCommand $command, AnnotationData $annotationData): void
    {
  //    $command->addOption(
  //      '--multidev-update',
  //      'u',
  //      InputOption::VALUE_NONE,
  //      'Create or update related multidev environment after successful deployment.',
  //    );
    }

  /**
   * @param \Consolidation\AnnotatedCommand\CommandData $commandData
   *
   * @return void
   * @throws \Exception
   */
    #[Hook(type: HookManager::ARGUMENT_VALIDATOR, target: DeployCommand::ARTIFACT_DEPLOY_COMMAND)]
    public function validate(CommandData $commandData): void
    {
        $pantheonRemotes = $this->getPantheonRemotes();
        if (!empty($pantheonRemotes)) {
          // Since a Pantheon repository is in the list of repositories to push to
          // we need to make sure that branch naming standards are met.
            $branchName = $commandData->input()->getOption('branch');
            if (is_string($branchName)) {
                $this->branchNameIsValid($branchName);
            }
            if (is_string($commandData->input()->getOption('tag'))) {
                throw new \Exception('Tagging is not supported for Pantheon repositories.');
            }
        }
    }

  /**
   * @return string[]
   */
    protected function getPantheonRemotes(): array
    {
        $configuredRemotes = $this->getConfigValue('git.remotes');
        $repository = $this->repository();
        $pantheonRemotes = [];
        foreach ($configuredRemotes as $remote) {
            if ($repository->isPantheonGitUrl($remote)) {
                $pantheonRemotes[] = $remote;
            }
        }
        return $pantheonRemotes;
    }

  /**
   * @param string $branchName
   *
   * @return void
   * @throws \Exception
   */
    protected function branchNameIsValid(string $branchName): void
    {
        $this->repository()->assertValidMultidevBranch($branchName);
    }

    protected function getPantheonRepoRegex(): string
    {
        return $this->repository()->gitRepoRegex();
    }

    /**
     * Built from the hook's own config/logger (not the container) so the
     * hook stays directly constructible in unit tests.
     */
    protected function repository(): PantheonRepository
    {
        return new PantheonRepository($this->getConfig(), $this->logger);
    }
}
