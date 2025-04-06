<?php

namespace DigitalPolygon\PolymerPantheon\Drupal\Polymer\Plugin\Template;

use DigitalPolygon\Polymer\Robo\Template\Template;

class QuicksilverYaml extends Template {

    public static function id(): string
    {
        return 'quicksilver-config';
    }

    public function description(): string
    {
        return 'Pantheon Terminus Quicksilver plugin configuration file.';
    }

    public function source(): string
    {
        return $this->getConfigValue('pantheon.file-mappings.quicksilver-config.source');
    }

    public function destination(): string
    {
        $this->config->set('user.home', $this->getHomeDirectory());
        return $this->getConfigValue('pantheon.file-mappings.quicksilver-config.destination');
    }

    public static function collections(): array
    {
        return array_merge(parent::collections(), ['pantheon', 'quicksilver']);
    }

    protected function getHomeDirectory(): string
    {
        if (isset($_SERVER['HOME'])) {
            return $_SERVER['HOME'];
        }

        if (isset($_SERVER['HOMEDRIVE']) && isset($_SERVER['HOMEPATH'])) {
            return $_SERVER['HOMEDRIVE'] . $_SERVER['HOMEPATH'];
        }

        if (isset($_SERVER['USERPROFILE'])) {
            return $_SERVER['USERPROFILE'];
        }

        throw new \RuntimeException('Cannot determine the home directory.');
    }

}
