<?php

namespace Carlitorweb\Component\Vapi\Api\Helper;

defined('_JEXEC') || die;

use Joomla\CMS\Factory;
use Joomla\Database\ParameterType;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

class VapiHelper
{
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
    public static function getModuleById(int $moduleId): object
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

    /**
     * Method to escape output.
     *
     * @param   string  $output  The output to escape.
     *
     * @return  string  The escaped output, null otherwise.
     *
     */
    public static function escape($output): ?string
    {
        return $output === null ? '' : htmlspecialchars($output, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Get the images attributes
     *
     * @param string  $images  The JSOn string getImageAttributes
     *
     * @return  array  A array with the images attributes object
     *
     * @example ['intro' => imageIntroAttributesObject, 'full' => imageFullAttributesObject]
     */
    public static function getImageAttributes(string $images): array
    {
        $_images  = json_decode($images);
        $_introImage = new \stdClass;
        $_fullImage = new \stdClass;

        if (!empty($_images->image_intro)) {
            $img = HTMLHelper::_('cleanImageURL', $_images->image_intro);

            $_introImage->src =  self::resolve($img->url);
            $_introImage->alt = empty($_images->image_intro_alt) && empty($_images->image_intro_alt_empty) ? false : self::escape($_images->image_intro_alt);


            if ($img->attributes['width'] > 0 && $img->attributes['height'] > 0) {
                $_introImage->width = $img->attributes['width'];
                $_introImage->height = $img->attributes['height'];
            }
        }

        if (!empty($_images->image_fulltext)) {
            $img = HTMLHelper::_('cleanImageURL', $_images->image_fulltext);

            $_fullImage->src = self::resolve($img->url);
            $_fullImage->alt = empty($_images->image_fulltext_alt) && empty($_images->image_fulltext_alt_empty) ? false : self::escape($_images->image_intro_alt);
            $_fullImage->caption = empty($_images->image_fulltext_caption) ? false : self::escape($_images->image_fulltext_caption);

            if ($img->attributes['width'] > 0 && $img->attributes['height'] > 0) {
                $_fullImage->width = $img->attributes['width'];
                $_fullImage->height = $img->attributes['height'];
            }
        }

        return ['intro' => $_introImage, 'full' => $_fullImage];
    }

    /**
     * Fully Qualified Domain name for the image url
     *
     * @param   string  $uri      The uri to resolve
     *
     * @return  string
     */
    protected static function resolve(string $uri): string
    {
        // Check if external URL.
        if (stripos($uri, 'http') !== 0) {
            return Uri::root() . $uri;
        }

        return $uri;
    }
}
