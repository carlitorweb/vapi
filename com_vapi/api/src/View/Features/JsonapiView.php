<?php

namespace Carlitorweb\Component\Vapi\Api\View\Features;

defined('_JEXEC') || die;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;

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
