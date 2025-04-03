<?php

namespace DigitalPolygon\PolymerPantheon\Drupal\Polymer\Plugin\Hooks;

use Consolidation\AnnotatedCommand\AnnotatedCommand;
use Consolidation\AnnotatedCommand\AnnotationData;
use Consolidation\AnnotatedCommand\Attributes\Hook;
use Consolidation\AnnotatedCommand\CommandData;
use Consolidation\AnnotatedCommand\Hooks\HookManager;
use DigitalPolygon\Polymer\Robo\Commands\Artifact\DeployCommand;
use DigitalPolygon\Polymer\Robo\Exceptions\BadConfigurationValueException;
use DigitalPolygon\Polymer\Robo\Tasks\TaskBase;

class PantheonArtifactHook extends TaskBase
{
    protected const PANTHEON_GIT_URL_REGEX = '/^ssh:\/\/codeserver\.dev\.[a-f0-9\-]+@codeserver\.dev\.[a-f0-9\-]+\.drush\.in:2222\/~\/repository\.git$/';
    protected const MULTIDEV_NAME_REGEX = '/^(?!.*-.*-)[a-z0-9\-]{1,11}$/';

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
        $pantheonRemotes = [];
        foreach ($configuredRemotes as $remote) {
            if (preg_match($this->getPantheonRepoRegex(), $remote)) {
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
      // From https://docs.pantheon.io/guides/multidev/create-multidev
      // Multidev branch names must be all lowercase, no more than 11 characters,
      // and can contain a dash (-).
      // Environments cannot be created with the following reserved names:

      // Check if branch is master and allow it.
        if ('master' === $branchName) {
          // Always allow pushes to Pantheon master.
            return;
        }

      // Assume all other branches are meant for multidev environments. Validate
      // against Pantheon's multidev environment naming requirements.
        if (!preg_match(self::MULTIDEV_NAME_REGEX, $branchName)) {
            throw new \Exception("Branch name '$branchName' is invalid. Pantheon multidev branch names must be all lowercase, no more than 11 characters, and can contain a dash (-).");
        }

      // The following names are reserved by Pantheon and cannot be used as
      // multidev environment names.
        $bannedMultidevNames = [
        'settings',
        'team',
        'support',
        'multidev',
        'debug',
        'files',
        'tags',
        'billing',
        ];
        if (in_array($branchName, $bannedMultidevNames, true)) {
            throw new \Exception("Branch name '$branchName' is invalid. Pantheon multidev branch names cannot be any of the following: " . implode(', ', $bannedMultidevNames));
        }
    }

    protected function getPantheonRepoRegex(): string
    {
        $configuredRegex = $this->getConfigValue('pantheon.git-repo-regex');
        try {
            if (is_string($configuredRegex)) {
                if (@preg_match($configuredRegex, 'asdfadf') === false) {
                    throw new BadConfigurationValueException('pantheon.git-repo-regex', $configuredRegex, self::PANTHEON_GIT_URL_REGEX);
                }
            }
        } catch (BadConfigurationValueException $e) {
            if ($this->logger) {
                $this->logger->warning($e->getMessage());
            }
        }
        return self::PANTHEON_GIT_URL_REGEX;
    }
}
