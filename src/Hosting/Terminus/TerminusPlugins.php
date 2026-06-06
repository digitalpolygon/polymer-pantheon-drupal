<?php

namespace DigitalPolygon\Polymer\polymer_pantheon_drupal\Hosting\Terminus;

/**
 * The Terminus plugins this extension knows about, and the hook selector
 * used to gate commands on a plugin being installed.
 *
 * Previously constants on PantheonFileCommands; lifted to Hosting/ so both
 * agnostic and Drupal-side command classes can reference them without
 * coupling to each other (PWT-130).
 */
final class TerminusPlugins
{
    /**
     * HookSelector name for the installed-plugin validator
     * (TerminusCommands::validateTerminusPluginsExists()).
     */
    public const VALIDATE_SELECTOR = 'validateTerminusPluginExists';

    public const QUICKSILVER = 'terminus-quicksilver-plugin';
    public const BUILD_TOOLS = 'terminus-build-tools-plugin';
    public const SECRETS_MANAGER = 'terminus-secrets-manager-plugin';

    /**
     * Normalize a `pantheon.terminus.plugins` config entry to an install spec.
     *
     * Entries are either a plain string (optionally with a version
     * constraint) or an array with `name` and optional `version` keys.
     */
    public static function normalizeSpec(mixed $value): string
    {
        if (is_array($value)) {
            if (empty($value['name']) || !is_string($value['name'])) {
                throw new \LogicException('Plugin cannot be installed without a name specified.');
            }
            $spec = $value['name'];
            if (!empty($value['version'])) {
                if (!is_string($value['version'])) {
                    throw new \LogicException('Plugin version must be a string.');
                }
                $spec .= ':' . $value['version'];
            }
            return $spec;
        }
        if (is_string($value)) {
            return $value;
        }
        throw new \LogicException('Plugin must be a string or an array with keys: plugin, version (optional).');
    }
}
