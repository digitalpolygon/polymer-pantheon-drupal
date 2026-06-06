<?php

namespace DigitalPolygon\Polymer\polymer_pantheon_drupal;

use DigitalPolygon\Polymer\polymer_pantheon_drupal\Drupal\EventSubscriber\DrupalEventsSubscriber;
use DigitalPolygon\Polymer\polymer_pantheon_drupal\Hosting\Pantheon\PantheonRepository;
use DigitalPolygon\Polymer\polymer_pantheon_drupal\Hosting\Terminus\TerminusClient;
use League\Container\Argument\ResolvableArgument;
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
          'pantheonTerminusClient',
          'pantheonRepository',
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
        $container->addShared('pantheonTerminusClient', TerminusClient::class)
            ->addArgument(new ResolvableArgument('config'));
        $container->addShared('pantheonRepository', PantheonRepository::class)
            ->addArgument(new ResolvableArgument('config'))
            ->addArgument(new ResolvableArgument('logger'));
    }
}
