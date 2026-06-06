<?php

namespace DigitalPolygon\Polymer\polymer_pantheon_drupal\Plugin\Commands\Drupal;

use Consolidation\AnnotatedCommand\Attributes\Command;
use DigitalPolygon\Polymer\Core\Robo\Commands\Template\TemplateCommand;
use DigitalPolygon\Polymer\Core\Robo\Tasks\TaskBase;
use DigitalPolygon\Polymer\polymer_pantheon_drupal\Plugin\Commands\Hosting\PantheonFileCommands;
use DigitalPolygon\Polymer\polymer_pantheon_drupal\Plugin\Commands\Hosting\TerminusCommands;
use DigitalPolygon\Polymer\polymer_pantheon_drupal\Plugin\Template\Drupal\DrushSiteYaml;
use Robo\Symfony\ConsoleIO;

/**
 * Drupal-coupled Pantheon commands (Drupal side of the seam). May call into
 * the Hosting side; never the other way around.
 */
final class PantheonDrupalCommands extends TaskBase
{
    public const COMMAND_FILES_SETUP       = 'pantheon:setup:drupal';
    public const COMMAND_CREATE_DRUSH_YAML = 'pantheon:files:generate-drush-site-yaml';

    /**
     * Generate Drush file that interacts with configured Pantheon environment.
     *
     * By default, the file is installed to
     * `[project-root]/drush/sites/[pantheon-site-name].site.yml`.
     *
     * @param \Robo\Symfony\ConsoleIO $io
     *
     * @return int
     */
    #[Command(name: self::COMMAND_CREATE_DRUSH_YAML)]
    public function generateDrushSiteYaml(ConsoleIO $io): int
    {
        return $this->commandInvoker->invokeCommand($io->input(), TemplateCommand::TEMPLATE_GENERATE_FILE_COMMAND, [
            'template' => DrushSiteYaml::id(),
        ]);
    }

    /**
     * Setup all optimal Pantheon integrations for Drupal.
     *
     * @param ConsoleIO $io
     * @return int
     */
    #[Command(name: self::COMMAND_FILES_SETUP)]
    public function pantheonFilesSetup(ConsoleIO $io): int
    {
        $commands = [
            self::COMMAND_CREATE_DRUSH_YAML,
            PantheonFileCommands::COMMAND_COPY_PANTHEON_FILE,
            PantheonFileCommands::COMMAND_QUICKSILVER_INSTALL_CONFIG,
            TerminusCommands::COMMAND_TERMINUS_PLUGINS,
            TerminusCommands::COMMAND_QUICKSILVER_INSTALL_PROFILES,
            'drupal:setup:site:files',
        ];

        foreach ($commands as $command) {
            $this->commandInvoker->invokeCommand($io->input(), $command);
        }

        return 0;
    }
}
