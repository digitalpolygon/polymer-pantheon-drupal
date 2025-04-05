<?php

namespace DigitalPolygon\PolymerPantheon\Drupal\Polymer;

use DigitalPolygon\Polymer\Robo\Template\TemplateInterface;
use DigitalPolygon\PolymerPantheon\Drupal\Polymer\Plugin\Template\GitHubWorkflows\PantheonPrMultidevCreate;
use DigitalPolygon\PolymerPantheon\Drupal\Polymer\Plugin\Template\GitHubWorkflows\PantheonPrMultidevDelete;
use DigitalPolygon\PolymerPantheon\Drupal\Polymer\Plugin\Template\GitHubWorkflows\PantheonPush;
use DigitalPolygon\PolymerPantheon\Drupal\Polymer\Plugin\Template\GitHubWorkflows\PantheonPushDev;
use DigitalPolygon\PolymerPantheon\Drupal\Polymer\Services\EventSubscriber\DrupalEventsSubscriber;
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

        $this->addTemplate(
            PantheonPush::id(),
            PantheonPush::collections(),
            PantheonPush::class,
        );
        $this->addTemplate(
            PantheonPushDev::id(),
            PantheonPushDev::collections(),
            PantheonPushDev::class,
        );
        $this->addTemplate(
            PantheonPrMultidevCreate::id(),
            PantheonPrMultidevCreate::collections(),
            PantheonPrMultidevCreate::class,
        );
        $this->addTemplate(
            PantheonPrMultidevDelete::id(),
            PantheonPrMultidevDelete::collections(),
            PantheonPrMultidevDelete::class,
        );
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

    protected function addTemplate(string $id, array $collections, string $concrete): void
    {
        $serviceId = TemplateInterface::SERVICE_PREFIX . $id;
        $this->providedTemplates[] = $serviceId;
        $definition = $this->getContainer()->add($serviceId, $concrete);
        foreach ($collections as $collection) {
            $definition->addTag('plugin.templates.collections.' . $collection);
        }
    }
}
