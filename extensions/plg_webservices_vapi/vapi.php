<?php

defined('_JEXEC') || die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\ApiRouter;
use Joomla\Router\Route;

class PlgWebservicesVapi extends CMSPlugin
{
    /**
     * Registers com_vapi API's routes in the application
     *
     * @param   ApiRouter  &$router  The API Routing object
     *
     * @return  void
     *
     */
    public function onBeforeApiRoute(&$router)
    {
        // Render a list of com_content articles using the specific module
        // params as filters for the articles model
        $this->createModuleSiteRoutes($router, 'v1/vapi/modules/:id', 'modules.displayModule');
    }

    /**
     *
     * @param \Joomla\CMS\Application\ApiApplication $app
     *
     * TODO: Delete this after merged https://github.com/joomla/joomla-cms/pull/39498
     */
    public function onAfterApiRoute($app): void
    {
        if ($app->input->getCmd('option') === 'com_vapi') {
            $app->input->set('format', 'json');
        }
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
    private function createModuleSiteRoutes(&$router, $baseName, $controller, $defaults = [], $publicGets = true): void
    {
        $defaults = [
            'component' => 'com_vapi',
            'public'    => $publicGets,
            'format'    => [
                'application/json'
            ]
        ];

        $routes   = [
            new Route(['GET'], $baseName, $controller, ['id' => '(\d+)'], $defaults),
        ];

        $router->addRoutes($routes);
    }
}
