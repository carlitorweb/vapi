<?php

defined('_JEXEC') || die;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

return new class implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        // Register service providers to the component's container
        $container->registerServiceProvider(new MVCFactory('\\Carlitorweb\\Component\\Vapi'));
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\Carlitorweb\\Component\\Vapi'));

        // Instantiate and set up our extension object
        $container->set(
            ComponentInterface::class,
            function (Container $container) {
                /**
                 * Instantiate the extension object
                 *
                 * No custom extension class then I use \Joomla\CMS\Extension\MVCComponent instead.
                 */
                $component = new \Carlitorweb\Component\Vapi\Administrator\Extension\VapiComponent(
                    $container->get(ComponentDispatcherFactoryInterface::class)
                );

                // Set up the extension object
                $component->setMVCFactory($container->get(MVCFactoryInterface::class));

                // Return the extension object
                return $component;
            }
        );
    }
};
