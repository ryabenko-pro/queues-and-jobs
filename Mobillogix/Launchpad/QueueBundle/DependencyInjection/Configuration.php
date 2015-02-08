<?php

namespace Mobillogix\Launchpad\QueueBundle\DependencyInjection;

use Mobillogix\Launchpad\QueueBundle\Entity\QueuedTask;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('mobillogix_launchpad_queue');

        $types = $rootNode->children()
            ->scalarNode("template")->defaultValue("MobillogixLaunchpadQueueBundle::base.html.twig")->end()
            ->scalarNode("em")->defaultValue("doctrine.orm.default_entity_manager")->end()
            ->arrayNode("types")->requiresAtLeastOneElement()->prototype('array')
            ->children()
            ->scalarNode("name")->isRequired()->end()
            ->scalarNode("priority")->defaultValue(QueuedTask::PRIORITY_MEDIUM)->end()
            ->scalarNode("class_name")->isRequired()->end();

        return $treeBuilder;
    }
}
