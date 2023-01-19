<?php

namespace Carlitorweb\Component\Vapi\Api\View\Modules;

defined('_JEXEC') || die;

use \Joomla\CMS\MVC\View\JsonView as BaseJsonView;
use \Joomla\CMS\MVC\View\GenericDataException;
use Carlitorweb\Component\Vapi\Api\Helper\VapiHelper;
use Joomla\CMS\HTML\HTMLHelper;
use \Carlitorweb\Component\Vapi\Api\Model\ModuleModel;

class JsonView extends BaseJsonView
{
    /**
     * @var  array $fieldsToRenderList Array of fields for listing objects
     */
    protected $fieldsToRenderList = [
        'id',
        'title',
        'alias',
        'displayDate',
        'images',
        'metadesc',
        'metakey',
        'params',
        'displayHits',
        'displayCategoryTitle',
        'category_alias',
        'categoryDescription',
        'categoryMetadesc',
        'categoryMetakey',
        'categoryImage',
        'displayAuthorName',
        'author_email',
        'contactData',
    ];

    /**
     * @var  array  $display  Extra params to prepare the articles
     */
    protected $display = array();

    /**
     * Constructor.
     *
     * @param   array  $config  A named configuration array for object construction.
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
     * Set the data to load
     */
    protected function setOutput(array $items = null): void
    {
        /** @var \Joomla\CMS\MVC\Model\ListModel $mainModel */
        $mainModel = $this->getModel();

        /** @var \Carlitorweb\Component\Vapi\Api\Model\ModuleModel $moduleModel */
        $moduleModel = $this->getModel('module');

        if ($items === null) {
            $items = [];

            foreach ($mainModel->getItems() as $item) {
                $_item   = $this->prepareItem($item, $moduleModel);
                $items[] = $this->getAllowedPropertiesToRender($_item);
            }
        }

        // Check for errors.
        if (\count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $this->_output = $items;
    }

    /**
     * @param  \stdClass $item  The article to prepare
     */
    protected function getAllowedPropertiesToRender($item): \stdClass
    {
        $allowedField = new \stdClass;

        foreach($item as $key => $value) {
            if (in_array($key, $this->fieldsToRenderList, true)) {
                $allowedField->$key = $value;
            }
        }

        return $allowedField;
    }

    /**
     * Prepare item before render.
     *
     * @param   object       $item  The model item
     * @param   ModuleModel  $moduleModel
     *
     * @return  object
     *
     */
    protected function prepareItem($item, $moduleModel)
    {
        $item->slug = $item->alias . ':' . $item->id;
        if ($this->display['show_date']) {
            $show_date_field = $this->display['show_date_field'];
            $item->displayDate = HTMLHelper::_('date', $item->$show_date_field, $this->display['show_date_format']);
        }

        $item->displayCategoryTitle = $this->display['show_category'] ? $item->category_title : '';
        if (array_key_exists('category', $this->_models)) {
            $categoryData = $this->getModel('category')->getItem($item->catid);

            $item->categoryDescription = $categoryData->description;
            $item->categoryMetadesc = $categoryData->metadesc;
            $item->categoryMetakey = $categoryData->metakey;

            $categoryParams = $categoryData->params;
            $item->categoryImage = HTMLHelper::_('cleanImageURL', $categoryParams['image']);
            $item->categoryImage->url = VapiHelper::resolve($item->categoryImage->url);
            $item->categoryImage->alt = VapiHelper::escape($categoryParams['image_alt']);
        }

        if (empty($item->created_by_alias)) {
            $contactData = $moduleModel->getContactData($item->created_by);

            if ($contactData) {
                $contactData->image = HTMLHelper::_('cleanImageURL', $contactData->image);
                $contactData->image->url = VapiHelper::resolve($contactData->image->url);
                $contactData->image->alt = $contactData->name;

                $item->contactData = $contactData;
            }
        }

        $item->displayHits          = $this->display['show_hits'] ? $item->hits : '';
        $item->displayAuthorName    = $this->display['show_author'] ? $item->author : '';

        $item->images = VapiHelper::getImageAttributes($item->images);

        return $item;
    }

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     */
    public function display($tpl = null)
    {
        // remove any string that could create an invalid JSON
        // such as PHP Notice, Warning, logs...
        ob_clean();

        // this will clean up any previously added headers, to start clean
        header_remove();

        $this->setOutput();

        parent::display($tpl);

        echo $this->document->render();
    }
}
