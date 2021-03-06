<?php

namespace Devtrw\StateBridgeBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class DevtrwStateBridgeExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration   = new Configuration();
        $processedConfig = $this->processConfiguration($configuration, $configs);

        foreach($processedConfig['states'] as $stateName => &$state) {
            $this->applyRoutePrefixes($state);
        }
        $container->setParameter('devtrw_state_bridge.states', $processedConfig['states']);

        $container->setParameter('devtrw_state_bridge.jsonp_callback_fn', $processedConfig['jsonp_callback_fn']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    private function applyRoutePrefixes(&$config, $routePrefix = '')
    {
        if (array_key_exists('route_prefix', $config)) {
            $routePrefix .= $config['route_prefix'];
            unset($config['route_prefix']);
        }

        if (empty($config['children'])) {
            return;
        }

        foreach ($config['children'] as &$child) {
            if (!empty($child['route'])) {
                $child['route'] = $routePrefix . $child['route'];
            }
            $this->applyRoutePrefixes($child, $routePrefix);
        }
    }
}
