<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use stdClass;
use Concrete\Core\Entity\File\File;
use Concrete\Core\Support\Facade\Config;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\File\Image\Thumbnail\Type\Type as ThumbType;

/**
 * Image helper to get image information using thumbnail types with fallback on the legacy thumbnailer
 * is instantiated with default general settings if none provided.
 *
 * [object Object]
 */
class Image
{
    const IMG_FOR_PRODUCT_LIST = 'product_list';
    const IMG_FOR_SINGLE_PRODUCT = 'single_product';
    const DEFAULT_SINGLE_PRODUCT_IMG_WIDTH = 720;
    const DEFAULT_SINGLE_PRODUCT_IMG_HEIGHT = 720;
    const DEFAULT_PRODUCT_LIST_IMG_WIDTH = 400;
    const DEFAULT_PRODUCT_LIST_IMG_HEIGHT = 280;
    const DEFAULT_PRODUCT_MODAL_IMG_WIDTH = 560;
    const DEFAULT_PRODUCT_MODAL_IMG_HEIGHT = 999;
    const DEFAULT_IMG_CROP = false;

    /** @var Application $app */
    protected $app;
    /** @var File $imgObj */
    protected $imgObj;
    /** @var stdClass $legacyThumbProps with properties width, height and crop */
    protected $legacyThumbProps;
    /** @var ThumbType $thumbType core thumbnail type */
    protected $thumbType = null;
    /** @var string $resizingScheme CamelCase version of either static::IMG_FOR_PRODUCT_LIST, static::IMG_FOR_SINGLE_PRODUCT or static::IMG_FOR_PRODUCT_MODAL */
    protected $resizingScheme;

    /**
     * @param string $resizingScheme
     * @param string $thumbTypeHandle
     * @param stdClass $legacyThumbProps {width: integer|null, height: integer|null, crop: boolean|null}
     */
    public function __construct($resizingScheme = 'product_list', $thumbTypeHandle = null, $legacyThumbProps = null)
    {
        $this->app = Application::getFacadeApplication();
        $this->legacyThumbProps = (object) ['width'=>false, 'height'=>false, 'crop'=>false];
        $this->setResizingScheme($resizingScheme);
        $this->setThumbType($thumbTypeHandle);

        if (!$legacyThumbProps) {
            $legacyThumbProps= (object) ['width'=>false, 'height'=>false, 'crop'=>false];
        }

        $this->setLegacyThumbnailingValues($legacyThumbProps);
    }

    /**
     * Sets whether we are using the single product or product list image thumbnailing settings.
     *
     * @param string $resizingScheme one of IMG_FOR_PRODUCT_LIST, IMG_FOR_SINGLE_PRODUCT or IMG_FOR_PRODUCT_MODAL. Defaults to IMG_FOR_PRODUCT_LIST
     */
    public function setResizingScheme($resizingScheme)
    {
        // making sure we got a correct value
        switch ($resizingScheme) {
            case static::IMG_FOR_PRODUCT_LIST:
            case static::IMG_FOR_SINGLE_PRODUCT:
                $this->resizingScheme = camelcase($resizingScheme);
                break;
            default:
                $this->resizingScheme = camelcase(static::IMG_FOR_PRODUCT_LIST);
                break;
        }
    }

    /**
     * Sets the Thumbnail Type to use. Defaults to the one selected in general settings if any.
     *
     * @param string $thumbTypeHandle
     */
    public function setThumbType($thumbTypeHandle)
    {
        // A thumb type handle was provided, let's use that
        if (!empty($thumbTypeHandle)) {
            $this->thumbType = ThumbType::getByHandle($thumbTypeHandle);
        }

        // No thumb type handle was provided or it didn't return a proper thumb type pbject
        // let's use the one from general settings if any
        if (empty($thumbTypeHandle) || !is_object($this->thumbType)) {
            $thumbTypeID = Config::get('community_store.default' . $this->resizingScheme . 'ThumbType');
            if (!empty($thumbTypeID)) {
                $this->thumbType = ThumbType::getByID($thumbTypeID);
            }
        }
    }

    /**
     * Sets the legacy thumbnailer's width, height, and crop values.
     *
     * @param stdClass $legacyThumbProps {width: integer|null, height: integer|null, crop: boolean|null}
     */
    protected function setLegacyThumbnailingValues($legacyThumbProps)
    {
        $this->setLegacyThumbnailWidth($legacyThumbProps->width ?? false);
        $this->setLegacyThumbnailHeight($legacyThumbProps->height ?? false);
        $this->setLegacyThumbnailCrop($legacyThumbProps->crop ?? null);
    }

    /**
     * Sets the legacy thumbnailer's width.
     *
     * @param int|string $width
     */
    public function setLegacyThumbnailWidth($width)
    {
        $this->legacyThumbProps = is_object($this->legacyThumbProps) ? $this->legacyThumbProps : (object) ['width'=>false, 'height'=>false, 'crop'=>false];

        $this->legacyThumbProps->width = (isset($width) && !empty($width)) ? (int) $width : (int) Config::get('community_store.default' . $this->resizingScheme . 'ImageWidth');

        if (!$this->legacyThumbProps->width) {
            $const = 'DEFAULT_' . strtoupper(uncamelcase($this->resizingScheme)) . '_IMG_WIDTH';
            $this->legacyThumbProps->width = constant('self::' . $const);
        }
    }

    /**
     * Sets the legacy thumbnailer's height.
     *
     * @param int|string $height
     */
    public function setLegacyThumbnailHeight($height)
    {
        $this->legacyThumbProps = is_object($this->legacyThumbProps) ? $this->legacyThumbProps : (object) ['width'=>false, 'height'=>false, 'crop'=>false];

        $this->legacyThumbProps->height = (isset($height) && !empty($height)) ? (int) $height : (int) Config::get('community_store.default' . $this->resizingScheme . 'ImageHeight');

        if (!$this->legacyThumbProps->height) {
            $const = 'DEFAULT_' . strtoupper(uncamelcase($this->resizingScheme)) . '_IMG_HEIGHT';
            $this->legacyThumbProps->height = constant('self::' . $const);
        }
    }

    /**
     * Sets the legacy thumbnailer's crop.
     *
     * @param bool $crop
     */
    public function setLegacyThumbnailCrop($crop)
    {
        $this->legacyThumbProps = is_object($this->legacyThumbProps) ? $this->legacyThumbProps : (object) ['width'=>false, 'height'=>false, 'crop'=>false];

        $this->legacyThumbProps->crop = isset($crop) ? (bool) $crop : (bool)Config::get('community_store.default' . $this->resizingScheme . 'Crop');
    }

    /**
     * Gets a thumbnail src and optionally a retinaSrc to display on a page.
     *
     * @param File $imgObj
     *
     * @return returns stdClass with property src and optionally retinaSrc
     * [object Object]
     */
    public function getThumbnail(File $imgObj)
    {
        return $this->getThumbTypeThumbnail($imgObj);
    }

    /**
     * Gets a thumbnail src and optionally a retinaSrc based on thumbnail type with fallback on legacy thumbnailer.
     *
     * @param File  stdClass with property src and optionally retinaSrc
     */
    public function getThumbTypeThumbnail(File $imgObj)
    {
        $thumb = new stdClass();
        $this->imgObj = $imgObj;

        if (is_object($this->thumbType)) {
            $baseVersion = $this->thumbType->getBaseVersion();
            $thumb->src = $imgObj->getThumbnailURL($baseVersion);

            if ($thumb->src) {
                $retinaVersion = $this->thumbType->getDoubledVersion();
                $thumb->retinaSrc = $imgObj->getThumbnailURL($retinaVersion);

                return $thumb;
            }
        }

        return $this->getLegacyThumbnail($imgObj);
    }

    /**
     * Gets a thumbnail src from the legacy thumbnailer.
     *
     * @param File  stdClass with property src
     */
    public function getLegacyThumbnail(File $imgObj)
    {
        $this->imgObj = $imgObj;
        $thumb = $this->app->make('helper/image')->getThumbnail($imgObj, $this->legacyThumbProps->width, $this->legacyThumbProps->height, $this->legacyThumbProps->crop);

        return $thumb;
    }
}
