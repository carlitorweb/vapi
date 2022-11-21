<?php

defined('_JEXEC') || die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\ApiRouter;
use Joomla\Router\Route;


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
        $this->createFeaturedRoutes($router, 'v1/vapi/features', 'features');
    }

    /**
     * Creates routes map for CRUD
     *
     * @param   ApiRouter   &$router        The API Routing object
     * @param   string      $baseName       The route pattern to use for matching
     * @param   string      $controller     The name of the controller that will handle the api request.
     * @param   array       $defaults       An array of default values that are used when the URL is matched.
     * @param   bool        $publicGets     Allow the public to make GET requests.
     *
     * @return  void
     *
     */
    private function createFeaturedRoutes(&$router, $baseName, $controller, $defaults = [], $publicGets = false): void
    {
        $defaults    = [
            'component'  => 'com_vapi',
            'public' => $publicGets
        ];

        $routes = [
            new Route(['GET'], $baseName, $controller . '.displayList', [], $defaults),
        ];

        $router->addRoutes($routes);
    }
}
