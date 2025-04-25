<?php

namespace DigitalPolygon\PolymerPantheon\Drupal\Polymer\Plugin\Commands;

use Consolidation\AnnotatedCommand\Attributes\Command;
use Consolidation\AnnotatedCommand\Attributes\HookSelector;
use DigitalPolygon\Polymer\Robo\Tasks\TaskBase;
use Robo\Exception\AbortTasksException;
use Robo\Symfony\ConsoleIO;

use function Laravel\Prompts\password;

final class NewRelicCommands extends TaskBase
{
    public const COMMAND_NEW_RELIC_SETUP = 'pantheon:new-relic:setup';
    public const TERMINUS_PLUGIN_SECRETS_MANAGER = 'terminus-secrets-manager-plugin';

    /**
     * Setup New Relic integration for your Pantheon application.
     *
     * @param ConsoleIO $io
     * @return int
     * @throws AbortTasksException
     * @throws \Robo\Exception\TaskException
     */
    #[Command(name: self::COMMAND_NEW_RELIC_SETUP)]
    #[HookSelector(name: PantheonFileCommands::VALIDATE_SELECTOR_TERMINUS_PLUGIN, value: self::TERMINUS_PLUGIN_SECRETS_MANAGER)]
    #[HookSelector(name: PantheonFileCommands::VALIDATE_SELECTOR_TERMINUS_PLUGIN, value: PantheonFileCommands::TERMINUS_PLUGIN_QUICKSILVER_ID)]
    public function setup(ConsoleIO $io): int
    {
        $newRelicEnabled = $this->getConfigValue('pantheon.new-relic.enable', false);
        $apiKey = $this->getConfigValue('pantheon.new-relic.api-key');
        $siteId = $this->getConfigValue('pantheon.site-info.name');
        $secretName = 'new_relic_api_key';
        if ($newRelicEnabled) {
            $terminusBin = $this->getConfigValue('pantheon.terminus.bin', 'terminus');
            try {
                $io->writeln("Checking to see if New Relic API key already exists in Pantheon secrets...");
                $this->execCommand("$terminusBin secret:site:list $siteId --filter=name=$secretName --format=string | grep $secretName");
                $io->writeln("New Relic API key already exists in Pantheon secrets.");
            } catch (AbortTasksException $e) {
                $io->error("Either the Pantheon secret '$secretName' does not exist or you do not have permission to view it. Check with your Pantheon application contact.");
                $io->writeln("Attempting to set the New Relic API key in Pantheon secrets...");
                if (!empty($apiKey)) {
                    $io->writeln("Using API key specified in configuration pantheon.new-relic.api-key.");
                } else {
                    $io->writeln("Preparing to set New Relic API key in Pantheon secrets. If you have not already done so, please create a New Relic API key at https://one.newrelic.com/launcher/api-keys-ui.api-keys-launcher and copy it to your clipboard.");
                    $io->writeln("Consider setting the API key in configuration pantheon.new-relic.api-key.");
                    $apiKey = password("Enter New Relic API Key:", '', true);
                }
                $result = $this->execCommand("$terminusBin secret:site:set $siteId $secretName $apiKey --type=runtime --scope=web --no-interaction");
                if ($result !== 0) {
                    $io->error("Failed to set New Relic API key in Pantheon secrets. You may not have permission to set secrets. Check with your Pantheon application contact.");
                    return 1;
                }
            }

            $io->writeln("Installing New Relic Quicksilver profile scripts...");
            $this->execCommand("$terminusBin quicksilver:profile new-relic --no-interaction");
        } else {
            $io->writeln("Polymer is not enabled for New Relic support. Set pantheon.new-relic.enable to true to enable New Relic support.");
        }
        return 0;
    }
}
