<?php

namespace DigitalPolygon\PolymerPantheon\Drupal\Polymer;

use DigitalPolygon\Polymer\Robo\Template\TemplateInterface;
use DigitalPolygon\PolymerPantheon\Drupal\Polymer\Plugin\Template\DrushSiteYaml;
use DigitalPolygon\PolymerPantheon\Drupal\Polymer\Plugin\Template\GitHubWorkflows\PantheonPrMultidevCreate;
use DigitalPolygon\PolymerPantheon\Drupal\Polymer\Plugin\Template\GitHubWorkflows\PantheonPrMultidevDelete;
use DigitalPolygon\PolymerPantheon\Drupal\Polymer\Plugin\Template\GitHubWorkflows\PantheonPush;
use DigitalPolygon\PolymerPantheon\Drupal\Polymer\Plugin\Template\GitHubWorkflows\PantheonPushDev;
use DigitalPolygon\PolymerPantheon\Drupal\Polymer\Plugin\Template\PantheonYaml;
use DigitalPolygon\PolymerPantheon\Drupal\Polymer\Plugin\Template\QuicksilverYaml;
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

        $this->addTemplates([
            PantheonYaml::class,
            DrushSiteYaml::class,
            QuicksilverYaml::class,
            PantheonPush::class,
            PantheonPushDev::class,
            PantheonPrMultidevCreate::class,
            PantheonPrMultidevDelete::class,
        ]);
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

    /**
     * @param string[] $templates
     * @return void
     */
    protected function addTemplates(array $templates): void
    {
        foreach ($templates as $template) {
            $this->addTemplate($template);
        }
    }

    protected function addTemplate(string $class): void
    {
        $id = call_user_func([$class, 'id']);
        $collections = call_user_func([$class, 'collections']);
        $serviceId = TemplateInterface::SERVICE_PREFIX . $id;
        $this->providedTemplates[] = $serviceId;
        $definition = $this->getContainer()->add($serviceId, $class);
        foreach ($collections as $collection) {
            $definition->addTag('plugin.templates.collections.' . $collection);
        }
    }
}
