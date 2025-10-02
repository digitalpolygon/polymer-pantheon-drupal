<?php

namespace DigitalPolygon\Polymer\polymer_pantheon_drupal;

use DigitalPolygon\Polymer\Core\Robo\Extension\PolymerExtensionBase;

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
