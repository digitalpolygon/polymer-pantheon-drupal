<?php

namespace DigitalPolygon\Polymer\polymer_pantheon_drupal;

use DigitalPolygon\Polymer\polymer_pantheon_drupal\Services\EventSubscriber\DrupalEventsSubscriber;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;

final class PolymerPantheonDrupalServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    protected array $providedTemplates = [];

  /**
   * {@inheritdoc}
   */
    public function boot(): void
    {
        $container = $this->getContainer();
        $container->extend('eventDispatcher')
          ->addMethodCall('addSubscriber', ['pantheonDrupalEventsSubscriber']);
    }

    /**
     * {@inheritdoc}
     */
    public function provides(string $id): bool
    {
        $provides = [
          'pantheonDrupalEventsSubscriber',
        ];
        return in_array($id, $provides);
    }

    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        $container = $this->getContainer();
        $container->addShared('pantheonDrupalEventsSubscriber', DrupalEventsSubscriber::class);
    }
}
