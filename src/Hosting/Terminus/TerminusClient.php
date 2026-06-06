<?php

namespace DigitalPolygon\Polymer\polymer_pantheon_drupal\Hosting\Terminus;

use Consolidation\Config\ConfigInterface;

/**
 * Builds Terminus command lines with a uniform, configurable binary.
 *
 * Agnostic Pantheon tooling (no Drupal references): the single source for
 * resolving `pantheon.terminus.bin` so every command class composes Terminus
 * invocations the same way. Registered as `pantheonTerminusClient`.
 */
final class TerminusClient
{
    public function __construct(private ConfigInterface $config)
    {
    }

    /**
     * The Terminus executable, from `pantheon.terminus.bin`.
     */
    public function bin(): string
    {
        $bin = $this->config->get('pantheon.terminus.bin', 'terminus');
        return is_string($bin) && $bin !== '' ? $bin : 'terminus';
    }

    /**
     * Compose a full Terminus command line.
     */
    public function command(string $args, bool $interactive = true): string
    {
        $command = $this->bin() . ' ' . $args;
        if (!$interactive) {
            $command .= ' --no-interaction';
        }
        return $command;
    }

    /**
     * Shell command that succeeds only when the given plugin is installed.
     */
    public function pluginIsInstalledCommand(string $plugin): string
    {
        return $this->command('self:plugin:list') . " | grep $plugin";
    }

    /**
     * Install a Terminus plugin (optionally with a version constraint spec).
     */
    public function pluginInstallCommand(string $pluginSpec, bool $interactive = true): string
    {
        return $this->command("self:plugin:install $pluginSpec", $interactive);
    }

    /**
     * Install a Quicksilver profile via the quicksilver plugin.
     */
    public function quicksilverProfileCommand(string $profile, bool $interactive = true): string
    {
        return $this->command("quicksilver:profile $profile", $interactive);
    }
}
