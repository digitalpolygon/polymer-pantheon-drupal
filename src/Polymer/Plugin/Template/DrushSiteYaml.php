<?php

namespace DigitalPolygon\PolymerPantheon\Drupal\Polymer\Plugin\Template;

use DigitalPolygon\Polymer\Robo\Template\Template;
use DigitalPolygon\Polymer\Robo\Template\Token;

class DrushSiteYaml extends Template {

    public static function id(): string
    {
        return 'pantheon-drush-site';
    }

    public function description(): string
    {
        return 'Pantheon Drush site file.';
    }

    public function source(): string
    {
        return $this->getConfigValue('pantheon.file-mappings.drush-site.source');
    }

    public function destination(): string
    {
        return $this->getConfigValue('pantheon.file-mappings.drush-site.destination');
    }

    public function tokens(): array
    {
        return [
            new Token('#site-id#', $this->getConfigValue('pantheon.site-info.id'), true),
            new Token('#site-name#', $this->getConfigValue('pantheon.site-info.name'), true),
        ];
    }

    public static function collections(): array
    {
        return array_merge(parent::collections(), ['pantheon', 'drush', 'drupal']);
    }
}
