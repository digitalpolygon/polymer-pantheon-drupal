<?php

namespace DigitalPolygon\PolymerPantheon\Drupal\Polymer;

use DigitalPolygon\Polymer\Robo\Extension\PolymerExtensionBase;

class ExtensionInfo extends PolymerExtensionBase
{
    /**
     * {@inheritdoc}
     */
    public static function getExtensionName(): string
    {
        return 'polymer_pantheon_drupal';
    }
}
