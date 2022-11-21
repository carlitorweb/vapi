<?php

namespace Carlitorweb\Component\Vapi\Api\Controller;

use Joomla\CMS\MVC\Controller\ApiController;

\defined('_JEXEC') or die;

class FeaturesController extends ApiController
{
    /**
     * @var $contentType Used as default for $modelName as well as
     * when outputting response as type object
     */
    protected $contentType = 'features';

    /**
     * @var $default_view Will be used as default for $viewName
     */
    protected $default_view = 'features';
}
