<?php

namespace Carlitorweb\Component\Vapi\Api\Controller;

defined('_JEXEC') || die;

use Joomla\CMS\MVC\Factory\ApiMVCFactory;
use Joomla\CMS\Application\ApiApplication;
use Joomla\Input\Input;
use Joomla\CMS\Language\Text;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\CMS\Component\ComponentHelper;

class ModuleController extends \Joomla\CMS\MVC\Controller\BaseController
{
    /**
     * @var string $default_view Will be used as default for $viewName
     */
    protected $default_view = 'modules';

    /**
     * @var \Joomla\Registry\Registry $moduleParams The module params to set filters in the model
     */
    protected $moduleParams;

    /**
     * Constructor.
     *
     * @param   array           $config   An optional associative array of configuration settings
     *
     * @param   ApiMVCFactory   $factory  The factory.
     * @param   ApiApplication  $app      The Application for the dispatcher
     * @param   Input           $input    Input
     *
     * @throws  \Exception
     */
    public function __construct($config = array(), ApiMVCFactory $factory = null, ?ApiApplication $app = null, ?Input $input = null)
    {
        if (\array_key_exists('moduleParams', $config)) {
            $this->moduleParams = new \Joomla\Registry\Registry($config['moduleParams']);
        }

        parent::__construct($config, $factory, $app, $input);
    }

    /**
     * Set the models and execute the view
     *
     * @throws  \Exception
     */
    public function displayModule(): void
    {
        $moduleID    = $this->input->get('id', 0, 'int');
        $moduleState = new \Joomla\Registry\Registry(['moduleID' => $moduleID]);

        /** @var \Carlitorweb\Component\Vapi\Api\Model\ModuleModel $moduleModel */
        $moduleModel = $this->factory->createModel('Module', 'Api', ['ignore_request' => true, 'state' => $moduleState]);

        // Set the params who will be used by the model
        if (empty($this->moduleParams)) {
            $this->setModuleParams($moduleModel);
        }

        $mainModel = $this->getMainModelForView($this->moduleParams);

        /** @var \Joomla\CMS\Document\JsonDocument $document */
        $document   = $this->app->getDocument();

        $viewType   = $document->getType();
        $viewName   = $this->input->get('view', $this->default_view);
        $viewLayout = $this->input->get('layout', 'default', 'string');

        try {
            /** @var \Carlitorweb\Component\Vapi\Api\View\Modules\JsonView $view */
            $view = $this->getView(
                $viewName,
                $viewType,
                '',
                ['moduleParams' => $this->moduleParams, 'base_path' => $this->basePath, 'layout' => $viewLayout]
            );
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }

        // Push the model into the view (as default)
        $view->setModel($mainModel, true);

        // Push as secondary model the Module model
        $view->setModel($moduleModel);

        $view->document = $this->app->getDocument();

        $view->display();
    }

    /**
     * Boot the model and set the states
     *
     * @param  \Joomla\Registry\Registry  $params The module params
     *
     */
    protected function getMainModelForView($params): \Joomla\Component\Content\Site\Model\ArticlesModel
    {
        $mvcContentFactory = $this->app->bootComponent('com_content')->getMVCFactory();

        // Get an instance of the generic articles model
        /** @var \Joomla\Component\Content\Site\Model\ArticlesModel $articlesModel */
        $articlesModel = $mvcContentFactory->createModel('Articles', 'Site', ['ignore_request' => true]);

        if (!$articlesModel) {
            throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_MODEL_CREATE'));
        }

        $appParams = ComponentHelper::getComponent('com_content')->getParams();
        $articlesModel->setState('params', $appParams);

        $articlesModel->setState('filter.published', ContentComponent::CONDITION_PUBLISHED);

        /*
         * Set the filters based on the module params
         */
        $articlesModel->setState('list.start', 0);
        $articlesModel->setState('list.limit', (int) $params->get('count', 0));

        $catids = $params->get('catid');
        $articlesModel->setState('filter.category_id', $catids);

        // Ordering
        $ordering = $params->get('article_ordering', 'a.ordering');
        $articlesModel->setState('list.ordering', $ordering);
        $articlesModel->setState('list.direction', $params->get('article_ordering_direction', 'ASC'));

        $articlesModel->setState('filter.featured', $params->get('show_front', 'show'));

        $excluded_articles = $params->get('excluded_articles', '');

        if ($excluded_articles) {
            $excluded_articles = explode("\r\n", $excluded_articles);
            $articlesModel->setState('filter.article_id', $excluded_articles);

            // Exclude
            $articlesModel->setState('filter.article_id.include', false);
        }

        return $articlesModel;
    }

    /**
     * Set the module params
     *
     * @param  \Carlitorweb\Component\Vapi\Api\Model\ModuleModel  $moduleModel
     *
     */
    protected function setModuleParams($moduleModel): \Joomla\Registry\Registry
    {
        // Get the module params
        $module = $moduleModel->getModule();

        if (is_null($module)) {
            throw new \UnexpectedValueException(
                sprintf(
                    '$module need be of type object, %s was returned in %s()',
                    gettype($module),
                    __FUNCTION__
                )
            );
        }

        return $this->moduleParams = new \Joomla\Registry\Registry($module->params);
    }
}
