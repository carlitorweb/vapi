<?php

namespace Carlitorweb\Component\Vapi\Api\View\Modules;

defined('_JEXEC') || die;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use Joomla\CMS\HTML\HTMLHelper;
use Carlitorweb\Component\Vapi\Api\Helper\VapiHelper;

class JsonapiView extends BaseApiView
{
    /**
     * @var  array $fieldsToRenderList Array of fields for listing objects
     */
    protected $fieldsToRenderList = [
        'id',
        'title',
        'displayAuthorName',
        'author_email',
        'alias',
        'displayCategoryTitle',
        'category_alias',
        'displayDate',
        'images',
        'metadesc',
        'metakey',
        'params',
        'displayHits',
    ];

    /**
     * @var array $params The display options to set in each item
     */
    protected $display = [];

    /**
     * Constructor.
     *
     * @param   array  $config  A named configuration array for object construction.
     *                          contentType: the name (optional) of the content type to use for the serialization
     *
     * @since   4.0.0
     */
    public function __construct($config = [])
    {
        if (\array_key_exists('moduleParams', $config)) {
            $params = $config['moduleParams'];

            // Display options
            $this->display['show_date']         = $params->get('show_date', 0);
            $this->display['show_date_field']   = $params->get('show_date_field', 'created');
            $this->display['show_date_format']  = $params->get('show_date_format', 'Y-m-d H:i:s');
            $this->display['show_category']     = $params->get('show_category', 0);
            $this->display['show_hits']         = $params->get('show_hits', 0);
            $this->display['show_author']       = $params->get('show_author', 0);
        }
        parent::__construct($config);
    }

    /**
     * Execute and display a template script.
     *
     * @param $items  Array of items
     *
     * @return  string
     *
     */
    public function displayList(array $items = null)
    {
        return parent::displayList($items);
    }

    /**
     * Prepare item before render.
     *
     * @param   object  $item  The model item
     *
     * @return  object
     *
     */
    protected function prepareItem($item)
    {
        $item->slug = $item->alias . ':' . $item->id;
        if ($this->display['show_date']) {
            $show_date_field = $this->display['show_date_field'];
            $item->displayDate = HTMLHelper::_('date', $item->$show_date_field, $this->display['show_date_format']);
        }

        $item->displayCategoryTitle = $this->display['show_category'] ? $item->category_title : '';
        $item->displayHits          = $this->display['show_hits'] ? $item->hits : '';
        $item->displayAuthorName    = $this->display['show_author'] ? $item->author : '';

        $item->images = VapiHelper::getImageAttributes($item->images);

        return parent::prepareItem($item);
    }
}
