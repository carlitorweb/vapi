<?php

namespace Carlitorweb\Component\Vapi\Api\Model;

defined('_JEXEC') || die;

use Joomla\CMS\Factory;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\Database\ParameterType;
use Joomla\CMS\Language\Text;

class ModuleModel extends \Joomla\CMS\MVC\Model\BaseDatabaseModel
{
    /**
     * Get the module
     *
     * @return  \stdClass|null The Module object
     *
     * @throws \InvalidArgumentException If was not set the module ID
     * @throws \RuntimeException If the module could not be found
     *
     */
    public function getModule(): ?object
    {
        /** @var \Joomla\CMS\Application\CMSApplicationInterface $app */
        $app = Factory::getApplication();

        $mid = $this->state->get('moduleID', 0);

        if ($mid === 0) {
            throw new \InvalidArgumentException(
                sprintf(
                    'A module ID is neccessary in %s',
                    __METHOD__
                )
            );
        }

        /** @var \Joomla\Database\DatabaseInterface $db */
        $db    = $this->getDatabase();
        $query = $this->getModuleQuery($db, $mid);

        // Set the query
        $db->setQuery($query);

        // Build a cache ID for the resulting data object
        $cacheId = 'com_vapi.moduleId' . $mid;

        try {
            /** @var \Joomla\CMS\Cache\Controller\CallbackController $cache */
            $cache  = Factory::getContainer()->get(CacheControllerFactoryInterface::class)
                ->createCacheController('callback', ['defaultgroup' => 'com_modules']);

            $module = $cache->get(array($db, 'loadObject'), array(), md5($cacheId), false);
        } catch (\RuntimeException $e) {
            $app->getLogger()->warning(
                Text::sprintf('JLIB_APPLICATION_ERROR_MODULE_LOAD', $e->getMessage()),
                array('category' => 'jerror')
            );

            return null;
        }

        return $module;
    }

    /**
     * Retrieve Contact
     *
     * @param   int  $userId  Id of the user who created the article
     *
     * @return  \stdClass|null  Object containing contact details or null if not found
     */
    public function getContactData($userId)
    {
        static $contacts = array();

        // Note: don't use isset() because value could be null.
        if (array_key_exists($userId, $contacts)) {
            return $contacts[$userId];
        }

        $db     = Factory::getContainer()->get('DatabaseDriver');
        $query  = $db->getQuery(true);
        $userId = (int) $userId;

        $query->select(
            $db->quoteName(
                [
                    'contact.name',
                    'contact.alias',
                    'contact.con_position',
                    'contact.webpage',
                    'contact.email_to',
                    'contact.misc',
                    'contact.image',
                ]
            )
        )
            ->from($db->quoteName('#__contact_details', 'contact'))
            ->where(
                [
                    $db->quoteName('contact.published') . ' = 1',
                    $db->quoteName('contact.user_id') . ' = :createdby',
                ]
            )
            ->bind(':createdby', $userId, ParameterType::INTEGER);

        $query->order($db->quoteName('contact.id') . ' DESC')
            ->setLimit(1);

        $db->setQuery($query);

        $contacts[$userId] = $db->loadObject();

        return $contacts[$userId];
    }

    /**
     * Get the module query
     *
     * @param  int                                 $mid The ID of the module
     * @param  \Joomla\Database\DatabaseInterface  $db
     *
     */
    private function getModuleQuery($db, $mid): \Joomla\Database\QueryInterface
    {
        $query = $db->getQuery(true);

        $query->select('*')
            ->from($db->quoteName('#__modules'))
            ->where(
                $db->quoteName('id') . ' = :moduleId'
            )
            ->bind(':moduleId', $mid, ParameterType::INTEGER);

        return $query;
    }
}
