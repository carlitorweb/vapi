<?php

namespace Carlitorweb\Component\Vapi\Api\Controller;

defined('_JEXEC') || die;

use Joomla\CMS\MVC\Controller\ApiController;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Registry\Registry;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Component\Content\Site\Model\ArticlesModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\Database\ParameterType;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;

/**
 * * NOTE: Also we can connect to my own model
 * $model = $this->getModel($this->contentType, 'Administrator');
 * $model->getItems();
 */


/**
 * Vapi basic service controller.
 *
 */
class ModulesController extends ApiController
{
    /**
     * @var string $contentType Used as default for $modelName as well as
     * when outputting response as type object
     */
    protected $contentType = 'com_vapi.modules';

    /**
     * @var string $default_view Will be used as default for $viewName
     */
    protected $default_view = 'modules';

    /**
     * @var Registry $moduleParams The params to set filters in the model
     */
    protected $moduleParams;

    /**
     * Get the articles features list
     *
     * @return  static  A BaseController object to support chaining.
     *
     */
    public function displayList()
    {
        $params = empty($this->moduleParams) ?  $this->setModuleParams() : $this->moduleParams;

        $factory = $this->app->bootComponent('com_content')->getMVCFactory();

        // Get an instance of the generic articles model
        /** @var ArticlesModel $articles */
        $articles = $factory->createModel('Articles', 'Site', ['ignore_request' => true]);

        if (!$articles) {
            throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_MODEL_CREATE'));
        }

        $appParams = ComponentHelper::getComponent('com_content')->getParams();
        $articles->setState('params', $appParams);

        $articles->setState('filter.published', ContentComponent::CONDITION_PUBLISHED);

        /*
         * Set the filters based on the module params
        */
        $articles->setState('list.start', 0);
        $articles->setState('list.limit', (int) $params->get('count', 0));

        $catids = $params->get('catid');
        $articles->setState('filter.category_id', $catids);

        // Ordering
        $ordering = $params->get('article_ordering', 'a.ordering');
        $articles->setState('list.ordering', $ordering);
        $articles->setState('list.direction', $params->get('article_ordering_direction', 'ASC'));

        $articles->setState('filter.featured', $params->get('show_front', 'show'));

        $excluded_articles = $params->get('excluded_articles', '');

        if ($excluded_articles) {
            $excluded_articles = explode("\r\n", $excluded_articles);
            $articles->setState('filter.article_id', $excluded_articles);

            // Exclude
            $articles->setState('filter.article_id.include', false);
        }

        $this->setView($articles);
        return $this;
    }

    /**
     * Set the view
     *
     * @param $model The model to use in the view
     */
    protected function setView(ArticlesModel $model): void
    {
        $viewType   = $this->app->getDocument()->getType();
        $viewName   = $this->input->get('view', $this->default_view);
        $viewLayout = $this->input->get('layout', 'default', 'string');

        try {
            /** @var \Joomla\CMS\MVC\View\JsonApiView $view */
            $view = $this->getView(
                $viewName,
                $viewType,
                '',
                ['moduleParams' => $this->moduleParams, 'base_path' => $this->basePath, 'layout' => $viewLayout, 'contentType' => $this->contentType]
            );
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }

        // Push the model into the view (as default)
        $view->setModel($model, true);

        $view->document = $this->app->getDocument();

        $view->displayList();
    }

    /**
     *  Set the module params
     *
     * @param int $id The module ID
     *
     * @return Registry The module params
     */
    protected function setModuleParams(?int $id = null): Registry
    {
        if ($id === null) {
            $id = $this->input->get('id', 0, 'int');
        }

        // Get the module params
        $module = $this->getModuleById($id);
        return $this->moduleParams = new Registry($module->params);
    }

    /**
     * Get module by id
     *
     * @param   int  $id  The id of the module
     *
     * @return  \stdClass  The Module object
     *
     * @throws \RuntimeException If the module could not be found
     *
     * @see \Joomla\CMS\Helper\ModuleHelper
     */
    protected function getModuleById(int $moduleId): object
    {
        /** @var \Joomla\CMS\Application\CMSApplicationInterface $app */
        $app    = Factory::getApplication();

        // Build a cache ID for the resulting data object
        $cacheId = 'moduleId' . $moduleId;

        /** @var \Joomla\Database\DatabaseDriver $db */
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query  = $db->getQuery(true);

        $query->select('m.*')
            ->from($db->quoteName('#__modules', 'm'))
            ->where(
                $db->quoteName('m.id') . ' = :moduleId'
            )
            ->bind(':moduleId', $moduleId, ParameterType::INTEGER);

        // Set the query
        $db->setQuery($query);

        try {
            /** @var \Joomla\CMS\Cache\Controller\CallbackController $cache */
            $cache = Factory::getContainer()->get(CacheControllerFactoryInterface::class)
                ->createCacheController('callback', ['defaultgroup' => 'com_modules']);

            $module = $cache->get(array($db, 'loadObject'), array(), md5($cacheId), false);
        } catch (\RuntimeException $e) {
            $app->getLogger()->warning(
                Text::sprintf('JLIB_APPLICATION_ERROR_MODULE_LOAD', $e->getMessage()),
                array('category' => 'jerror')
            );

            return array();
        }

        return $module;
    }
}
