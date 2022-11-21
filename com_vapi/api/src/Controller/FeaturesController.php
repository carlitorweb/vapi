<?php

namespace Carlitorweb\Component\Vapi\Api\Controller;

defined('_JEXEC') || die;

use Joomla\CMS\MVC\Controller\ApiController;


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

    /**
     * Article list view amended to add filtering of data
     *
     * @return  static  A BaseController object to support chaining.
     *
     */
    public function displayList()
    {
        return parent::displayList();
    }
}
