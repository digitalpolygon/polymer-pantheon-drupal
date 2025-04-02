<?php

namespace DigitalPolygon\PolymerPantheon\Drupal\Polymer\Plugin\Commands;

use Consolidation\AnnotatedCommand\Attributes\Command;
use DigitalPolygon\Polymer\Robo\Tasks\TaskBase;
use Robo\Symfony\ConsoleIO;
use Symfony\Component\Yaml\Yaml;

final class PantheonFileCommands extends TaskBase
{
    public const COPY_PANTHEON_YML_COMMAND        = 'pantheon:files:copy-pantheon-yml';
    public const INJECT_QUICKSILVER_HOOKS_COMMAND = 'pantheon:quicksilver-scripts:inject-hooks';
    public const COPY_QUICKSILVER_SCRIPTS_COMMAND = 'pantheon:files:copy-quicksilver-scripts';
    public const PANTHEON_FILES_SETUP_COMMAND     = 'pantheon:files:setup:drupal';
    public const CREATE_DRUSH_SITE_YAML_COMMAND   = 'pantheon:files:generate-drush-site-yaml';

    #[Command(name: self::INJECT_QUICKSILVER_HOOKS_COMMAND)]
    public function injectQuicksilverHooks(ConsoleIO $io): int
    {
        $extensionRoot = $this->getConfigValue('extension.polymer_pantheon_drupal.root');
        $repoRoot = $this->getConfigValue('repo.root');
        $pantheonYmlSrc = $extensionRoot . '/pantheon_files/pantheon.yml';
        $pantheonYmlDest = $repoRoot . '/pantheon.yml';
        if (!file_exists($pantheonYmlDest)) {
            $this->commandInvoker->invokeCommand($io->input(), self::COPY_PANTHEON_YML_COMMAND);
        } else {
            $pantheonYmlSrcData = Yaml::parseFile($pantheonYmlSrc);
            $pantheonYmlDestData = Yaml::parseFile($pantheonYmlDest);
            $sourceWorkflows = ['workflows' => $pantheonYmlSrcData['workflows']];
            $mergedDestinationData = array_merge_recursive($pantheonYmlDestData, $sourceWorkflows);
            $mergedDestinationYaml = Yaml::dump($mergedDestinationData, 5, 2);
            file_put_contents($pantheonYmlDest, $mergedDestinationYaml);
        }
        return 0;
    }

    #[Command(name: self::COPY_QUICKSILVER_SCRIPTS_COMMAND)]
    public function copyQuicksilverHooks(ConsoleIO $io): int
    {
        $extensionRoot = $this->getConfigValue('extension.polymer_pantheon_drupal.root');
        $docroot = $this->getConfigValue('docroot', 'web');
        $quicksilverFilesSourceDir = $extensionRoot . '/pantheon_files/quicksilver';
        $quicksilverFilesDestDir = $docroot . '/private/scripts';
        $result = $this->taskFilesystemStack()
          ->mkdir($quicksilverFilesDestDir)
          ->mirror($quicksilverFilesSourceDir, $quicksilverFilesDestDir)
          ->run();
        return $result->getExitCode();
    }

    #[Command(name: self::COPY_PANTHEON_YML_COMMAND)]
    public function copyPantheonYml(ConsoleIO $io): int
    {
        $repoRoot = $this->getConfigValue('repo.root');
        $extensionRoot = $this->getConfigValue('extension.polymer_pantheon_drupal.root');
        $pantheonYmlDest = $repoRoot . '/pantheon.yml';
        $pantheonYmlSrc = $extensionRoot . '/pantheon_files/pantheon.yml';
        $result = $this->taskFilesystemStack()
          ->copy($pantheonYmlSrc, $pantheonYmlDest)
          ->run();
        return $result->getExitCode();
    }

    #[Command(name: self::PANTHEON_FILES_SETUP_COMMAND)]
    public function pantheonFilesSetup(ConsoleIO $io): int
    {
        $repoRoot = $this->getConfigValue('repo.root');
        $pantheonYmlDest = $repoRoot . '/pantheon.yml';
        $commands = [
            self::COPY_QUICKSILVER_SCRIPTS_COMMAND,
            self::CREATE_DRUSH_SITE_YAML_COMMAND,
        ];

        if (file_exists($pantheonYmlDest)) {
            $commands[] = self::INJECT_QUICKSILVER_HOOKS_COMMAND;
        } else {
            $commands[] = self::COPY_PANTHEON_YML_COMMAND;
        }

        foreach ($commands as $command) {
            $this->commandInvoker->invokeCommand($io->input(), $command);
        }

        return 0;
    }

    #[Command(name: self::CREATE_DRUSH_SITE_YAML_COMMAND)]
    public function generateDrushSiteYaml(ConsoleIO $io): int
    {
        $extensionRoot = $this->getConfigValue('extension.polymer_pantheon_drupal.root');
        $repoRoot = $this->getConfigValue('repo.root');
        $drushSiteTemplateFile = $extensionRoot . '/pantheon_files/drush.site.yml';
        $site_id = $this->getConfigValue('pantheon.site-info.id');
        $site_name = $this->getConfigValue('pantheon.site-info.name');
        if (is_string($site_name) && is_string($site_id)) {
            $destinationFile = $repoRoot . '/drush/sites/' . $site_name . '.site.yml';
            $result = $this->_mkdir(dirname($destinationFile));
            if ($result->getExitCode() === 0) {
                $content = file_get_contents($drushSiteTemplateFile);
                $content = str_replace('#site-id#', $site_id, $content);
                $content = str_replace('#site-name#', $site_name, $content);
                file_put_contents($destinationFile, $content);
                $io->say('<info>Wrote ' . $destinationFile . '</info>');
            }
        } else {
            $this->logger?->warning("Either pantheon.site-info.id or pantheon.site-info.name is not set. Cannot write Drush site file.");
        }
        return 0;
    }
}
