<?php

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\ApiRouter;
use Joomla\Router\Route;

\defined('_JEXEC') or die;

class PlgWebservicesVapi extends CMSPlugin
{
    /**
     * Registers com_vapi's API's routes in the application
     *
     * @param   ApiRouter  &$router  The API Routing object
     *
     * @return  void
     *
     */
    public function onBeforeApiRoute(&$router)
    {
        $routes = [
            new Route(['GET'], 'v1/vapi/features', 'features.displayList'),
        ];

        $router->addRoutes($routes);
    }
}
