<?php

namespace Carlitorweb\Component\Vapi\Api\Helper;

defined('_JEXEC') || die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

class VapiHelper
{
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
    public static function resolve(string $uri): string
    {
        // Check if external URL.
        if (stripos($uri, 'http') !== 0) {
            return Uri::root() . $uri;
        }

        return $uri;
    }
}
