<?php

namespace DigitalPolygon\PolymerPantheon\Drupal\Polymer\Plugin\Commands;

use Consolidation\AnnotatedCommand\Attributes\Command;
use Consolidation\AnnotatedCommand\Attributes\Hook;
use Consolidation\AnnotatedCommand\Attributes\HookSelector;
use Consolidation\AnnotatedCommand\Attributes\Option;
use Consolidation\AnnotatedCommand\CommandData;
use Consolidation\AnnotatedCommand\Hooks\HookManager;
use DigitalPolygon\Polymer\Robo\Commands\Template\TemplateCommand;
use DigitalPolygon\Polymer\Robo\Tasks\TaskBase;
use DigitalPolygon\PolymerPantheon\Drupal\Polymer\Exception\TerminusPluginNotInstalledException;
use DigitalPolygon\PolymerPantheon\Drupal\Polymer\Plugin\Template\DrushSiteYaml;
use DigitalPolygon\PolymerPantheon\Drupal\Polymer\Plugin\Template\PantheonYaml;
use DigitalPolygon\PolymerPantheon\Drupal\Polymer\Plugin\Template\QuicksilverYaml;
use Robo\Contract\VerbosityThresholdInterface;
use Robo\Symfony\ConsoleIO;

final class PantheonFileCommands extends TaskBase
{
    public const COMMAND_COPY_PANTHEON_FILE            = 'pantheon:files:copy-pantheon-yml';
    public const COMMAND_TERMINUS_PLUGINS              = 'pantheon:terminus:plugins:install';
    public const COMMAND_QUICKSILVER_INSTALL_CONFIG    = 'pantheon:quicksilver:install-configuration';
    public const COMMAND_QUICKSILVER_INSTALL_PROFILES  = 'pantheon:quicksilver:install-profile';
    public const COMMAND_FILES_SETUP                   = 'pantheon:files:setup:drupal';
    public const COMMAND_CREATE_DRUSH_YAML             = 'pantheon:files:generate-drush-site-yaml';
    public const VALIDATE_SELECTOR_TERMINUS_PLUGIN     = 'validateTerminusPluginExists';
    public const TERMINUS_PLUGIN_QUICKSILVER_ID        = 'terminus-quicksilver-plugin';
    public const TERMINUS_PLUGIN_BUILD_TOOLS_ID        = 'terminus-build-tools-plugin';

    #[Command(name: self::COMMAND_QUICKSILVER_INSTALL_CONFIG)]
    public function installQuicksilverConfiguration(ConsoleIO $io): int
    {
        return $this->commandInvoker->invokeCommand($io->input(), TemplateCommand::TEMPLATE_GENERATE_FILE_COMMAND, [
            'template' => QuicksilverYaml::id(),
        ]);
    }

    #[Command(name: self::COMMAND_CREATE_DRUSH_YAML)]
    public function generateDrushSiteYaml(ConsoleIO $io): int
    {
        return $this->commandInvoker->invokeCommand($io->input(), TemplateCommand::TEMPLATE_GENERATE_FILE_COMMAND, [
            'template' => DrushSiteYaml::id(),
        ]);
    }

    #[Command(name: self::COMMAND_COPY_PANTHEON_FILE)]
    #[Option(name: 'force', description: 'Force copy of pantheon.yml file')]
    public function copyPantheonSettingsFile(ConsoleIO $io, bool $force = false): int
    {
        return $this->commandInvoker->invokeCommand($io->input(), TemplateCommand::TEMPLATE_GENERATE_FILE_COMMAND, [
            'template' => PantheonYaml::id(),
            '--force' => $force,
        ]);
    }

    #[Command(name: self::COMMAND_QUICKSILVER_INSTALL_PROFILES)]
    #[HookSelector(name: self::VALIDATE_SELECTOR_TERMINUS_PLUGIN, value: self::TERMINUS_PLUGIN_QUICKSILVER_ID)]
    public function installQuicksilverProfiles(ConsoleIO $io): int
    {
        $profiles = $this->getConfigValue('pantheon.quicksilver.install-profiles');
        $terminusBin = $this->getConfigValue('pantheon.terminus.bin', 'terminus');
        $task = $this->taskExecStack()
            ->printOutput(false)
            ->printMetadata(false)
            ->stopOnFail()
            ->interactive($io->input()->isInteractive());
        foreach ($profiles as $profile) {
            $command = "$terminusBin quicksilver:profile $profile";
            if (!$io->input()->isInteractive()) {
                $command .= ' --no-interaction';
            }
            $task->exec($command);
        }
        $result = $task->run();
        return 0;
    }

    #[Command(name: self::COMMAND_TERMINUS_PLUGINS)]
    public function installTerminusPlugins(ConsoleIO $io): int
    {
        $terminusPlugins = $this->getConfigValue('pantheon.terminus.plugins');
        $terminusBin = $this->getConfigValue('pantheon.terminus.bin', 'terminus');
        $task = $this->taskExecStack()
            ->printOutput(false)
            ->printMetadata(false)
            ->stopOnFail()
            ->interactive($io->input()->isInteractive());
        foreach ($terminusPlugins as $value)
        {
            if (is_array($value)) {
                if (empty($value['name']) || !is_string($value['name'])) {
                    throw new \LogicException("Plugin cannot be installed without a name specified.");
                }
                $plugin = $value['name'];
                if (!empty($value['version'])) {
                    if (!is_string($value['version'])) {
                        throw new \LogicException("Plugin version must be a string.");
                    }
                    else {
                        $plugin .= ':' . $value['version'];
                    }
                }
            }
            elseif (is_string($value)) {
                $plugin = $value;
            }
            else {
                throw new \LogicException("Plugin must be a string or an array with keys: plugin, version (optional).");
            }
            $command = "$terminusBin self:plugin:install $plugin";
            if (!$io->input()->isInteractive()) {
                $command .= ' --no-interaction';
            }
            $task->exec($command);
        }
        $result = $task->run();
        if ($result->getExitCode() !== 0) {
            $this->logger?->error('Error installing Terminus plugins.');
        }
        return $result->getExitCode();
    }

    #[Hook(type: HookManager::ARGUMENT_VALIDATOR, selector: self::VALIDATE_SELECTOR_TERMINUS_PLUGIN)]
    public function validateTerminusPluginsExists(CommandData $commandData): void {
        $plugins = $commandData->annotationData()->getList(self::VALIDATE_SELECTOR_TERMINUS_PLUGIN);
        $task = $this->taskExecStack()
            ->printOutput(false)
            ->printMetadata(false)
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
            ->stopOnFail()
            ->interactive($this->input()->isInteractive());
        foreach ($plugins as $plugin) {
            $task->exec("terminus self:plugin:list | grep $plugin");
        }
        $result = $task->run();
        if ($result->getExitCode() !== 0 && isset($plugin)) {
            throw new TerminusPluginNotInstalledException($plugin);
        }
    }

    #[Command(name: self::COMMAND_FILES_SETUP)]
    public function pantheonFilesSetup(ConsoleIO $io): int
    {
        $commands = [
            self::COMMAND_CREATE_DRUSH_YAML,
            self::COMMAND_COPY_PANTHEON_FILE,
            self::COMMAND_QUICKSILVER_INSTALL_CONFIG,
            self::COMMAND_TERMINUS_PLUGINS,
            self::COMMAND_QUICKSILVER_INSTALL_PROFILES,
            'drupal:setup:site:files',
        ];

        foreach ($commands as $command) {
            $this->commandInvoker->invokeCommand($io->input(), $command);
        }

        return 0;
    }

}
