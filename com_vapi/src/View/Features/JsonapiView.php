<?php

namespace Carlitorweb\Component\Vapi\Api\View\Features;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;

\defined('_JEXEC') or die;

class JsonapiView extends BaseApiView
{
    /**
     * @var $fieldsToRenderList Array of fields for listing objects
     */
    protected $fieldsToRenderList = [
        'id',
        'title',
        'alias',
    ];
}
