<?php

namespace DigitalPolygon\Polymer\polymer_pantheon_drupal\Plugin\Commands\Hosting;

use Consolidation\AnnotatedCommand\Attributes\Command;
use Consolidation\AnnotatedCommand\Attributes\Hook;
use Consolidation\AnnotatedCommand\Attributes\HookSelector;
use Consolidation\AnnotatedCommand\CommandData;
use Consolidation\AnnotatedCommand\Hooks\HookManager;
use DigitalPolygon\Polymer\Core\Robo\Tasks\TaskBase;
use DigitalPolygon\Polymer\polymer_pantheon_drupal\Hosting\Terminus\TerminusClient;
use DigitalPolygon\Polymer\polymer_pantheon_drupal\Hosting\Terminus\TerminusPlugins;
use Robo\Contract\VerbosityThresholdInterface;
use Robo\Symfony\ConsoleIO;

/**
 * Agnostic Terminus plugin/profile management (Hosting side of the seam).
 */
final class TerminusCommands extends TaskBase
{
    public const COMMAND_TERMINUS_PLUGINS             = 'pantheon:terminus:plugins:install';
    public const COMMAND_QUICKSILVER_INSTALL_PROFILES = 'pantheon:quicksilver:install-profile';

    /**
     * Installs configured Quicksilver profiles.
     *
     * See https://github.com/pantheon-systems/terminus-quicksilver-plugin.
     *
     * @param \Robo\Symfony\ConsoleIO $io
     *
     * @return int
     * @throws \Robo\Exception\TaskException
     */
    #[Command(name: self::COMMAND_QUICKSILVER_INSTALL_PROFILES)]
    #[HookSelector(name: TerminusPlugins::VALIDATE_SELECTOR, value: TerminusPlugins::QUICKSILVER)]
    public function installQuicksilverProfiles(ConsoleIO $io): int
    {
        $profiles = $this->getConfigValue('pantheon.quicksilver.install-profiles');
        $enableNewRelic = $this->getConfigValue('pantheon.new-relic.enable', false);
        if ($enableNewRelic) {
            $profiles[] = 'new-relic';
        }
        $terminus = $this->terminus();
        $task = $this->taskExecStack()
            ->printOutput(false)
            ->printMetadata(false)
            ->stopOnFail()
            ->interactive($io->input()->isInteractive());
        foreach ($profiles as $profile) {
            $task->exec($terminus->quicksilverProfileCommand($profile, $io->input()->isInteractive()));
        }
        $result = $task->run();
        return 0;
    }

    /**
     * Install Terminus plugins required by Polymer Pantheon Drupal extension.
     *
     * @param \Robo\Symfony\ConsoleIO $io
     *
     * @return int
     * @throws \Robo\Exception\TaskException
     */
    #[Command(name: self::COMMAND_TERMINUS_PLUGINS)]
    public function installTerminusPlugins(ConsoleIO $io): int
    {
        $terminusPlugins = $this->getConfigValue('pantheon.terminus.plugins');
        $terminus = $this->terminus();
        $task = $this->taskExecStack()
            ->printOutput(false)
            ->printMetadata(false)
            ->stopOnFail()
            ->interactive($io->input()->isInteractive());
        foreach ($terminusPlugins as $value) {
            $plugin = TerminusPlugins::normalizeSpec($value);
            $task->exec($terminus->pluginInstallCommand($plugin, $io->input()->isInteractive()));
        }
        $result = $task->run();
        if ($result->getExitCode() !== 0) {
            $this->logger?->error('Error installing Terminus plugins.');
        }
        return $result->getExitCode();
    }

    #[Hook(type: HookManager::ARGUMENT_VALIDATOR, selector: TerminusPlugins::VALIDATE_SELECTOR)]
    public function validateTerminusPluginsExists(CommandData $commandData): void
    {
        $plugins = $commandData->annotationData()->getList(TerminusPlugins::VALIDATE_SELECTOR);
        $terminus = $this->terminus();
        $task = $this->taskExecStack()
            ->printOutput(false)
            ->printMetadata(false)
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
            ->stopOnFail()
            ->interactive($this->input()->isInteractive());
        foreach ($plugins as $plugin) {
            $task->exec($terminus->pluginIsInstalledCommand($plugin));
        }
        $result = $task->run();
        if ($result->getExitCode() !== 0) {
            throw new \RuntimeException('Some Terminus plugins are not installed. To install configured plugins, run: <info>polymer ' . self::COMMAND_TERMINUS_PLUGINS . '</info>.');
        }
    }

    private function terminus(): TerminusClient
    {
        /** @var TerminusClient $client */
        $client = $this->getContainer()->get('pantheonTerminusClient');
        return $client;
    }
}
