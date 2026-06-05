<?php

namespace DigitalPolygon\Polymer\polymer_pantheon_drupal\Hosting\Exception;

class TerminusPluginNotInstalledException extends \RuntimeException
{
    public function __construct(string $pluginId)
    {
        parent::__construct(sprintf('The Terminus plugin "%s" is not installed.', $pluginId));
    }
}
