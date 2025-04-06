<?php

namespace DigitalPolygon\PolymerPantheon\Drupal\Polymer\Exception;

class TerminusPluginNotInstalledException extends \RuntimeException
{
    public function __construct(string $pluginId)
    {
        parent::__construct(sprintf('The Terminus plugin "%s" is not installed.', $pluginId));
    }
}
