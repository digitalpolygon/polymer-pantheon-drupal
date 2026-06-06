<?php

namespace DigitalPolygon\Polymer\polymer_pantheon_drupal\Plugin\Commands\Hosting;

use Consolidation\AnnotatedCommand\Attributes\Command;
use Consolidation\AnnotatedCommand\Attributes\Option;
use DigitalPolygon\Polymer\Core\Robo\Commands\Template\TemplateCommand;
use DigitalPolygon\Polymer\Core\Robo\Tasks\TaskBase;
use DigitalPolygon\Polymer\polymer_pantheon_drupal\Plugin\Template\Hosting\PantheonYaml;
use DigitalPolygon\Polymer\polymer_pantheon_drupal\Plugin\Template\Hosting\QuicksilverYaml;
use Robo\Symfony\ConsoleIO;

/**
 * Agnostic Pantheon platform-file generation (Hosting side of the seam).
 */
final class PantheonFileCommands extends TaskBase
{
    public const COMMAND_COPY_PANTHEON_FILE         = 'pantheon:files:copy-pantheon-yml';
    public const COMMAND_QUICKSILVER_INSTALL_CONFIG = 'pantheon:quicksilver:install-configuration';

    /**
     * Install the Quicksilver plugin configuration file.
     *
     * By default, the file is installed to `~/.quicksilver/quicksilver.yml`.
     *
     * @param \Robo\Symfony\ConsoleIO $io
     *
     * @return int
     */
    #[Command(name: self::COMMAND_QUICKSILVER_INSTALL_CONFIG)]
    public function installQuicksilverConfiguration(ConsoleIO $io): int
    {
        return $this->commandInvoker->invokeCommand($io->input(), TemplateCommand::TEMPLATE_GENERATE_FILE_COMMAND, [
            'template' => QuicksilverYaml::id(),
        ]);
    }

    /**
     * Add pantheon.yml to the project root.
     *
     * @param \Robo\Symfony\ConsoleIO $io
     * @param bool $force
     *
     * @return int
     */
    #[Command(name: self::COMMAND_COPY_PANTHEON_FILE)]
    #[Option(name: 'force', description: 'Force copy of pantheon.yml file')]
    public function copyPantheonSettingsFile(ConsoleIO $io, bool $force = false): int
    {
        return $this->commandInvoker->invokeCommand($io->input(), TemplateCommand::TEMPLATE_GENERATE_FILE_COMMAND, [
            'template' => PantheonYaml::id(),
            '--force' => $force,
        ]);
    }
}
