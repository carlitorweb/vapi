<?php

namespace Carlitorweb\Component\Vapi\Administrator\Extension;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Psr\Container\ContainerInterface;
use Joomla\CMS\Factory;

/**
 * Component class for com_vapi
 *
 */
class VapiComponent extends MVCComponent implements BootableExtensionInterface
{
    protected static $dic;

    public function boot(ContainerInterface $container)
    {
        self::$dic = $container;
    }

    public static function getContainer()
    {
        if (empty(self::$dic)) {
            Factory::getApplication()
                ->bootComponent('com_vapi');
        }

        return self::$dic;
    }
}
