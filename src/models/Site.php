<?php

namespace Lifeboat\Models;

use Lifeboat\App;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBVarchar;
use SilverStripe\ORM\ValidationException;

/**
 * Class Site
 *
 * @package Lifeboat\Models
 *
 * @property DBVarchar $SITE_KEY
 * @property DBVarchar $SITE_HOST
 */
class Site extends DataObject {

    /** @var App */
    private static $_app = null;

    private static $APP_ID      = '';
    private static $APP_SECRET  = '';

    private static $db = [
        'SITE_KEY'  => DBVarchar::class,
        'SITE_HOST' => DBVarchar::class
    ];

    public function __construct($record = [], $creationType = self::CREATE_OBJECT, $queryParams = [])
    {
        parent::__construct($record, $creationType, $queryParams);

        // Initialise app variable
        self::app();
    }

    /**
     * @return Site|null
     */
    public static function curr(): ?Site
    {
        if (self::app()->getActiveSite()) {
            try {
                return self::find_or_create(self::app()->getSiteKey(), self::app()->getHost());
            } catch (ValidationException $e) {
                error_log($e);
            }
        }

        return null;
    }

    /**
     * @param string $site_key
     * @param string $host
     * @return static
     * @throws ValidationException
     */
    public static function find_or_create(string $site_key, string $host = ''): Site
    {
        $find   = static::get()->find('SITE_KEY:ExactMatch', $site_key);

        if (!$find || !$find->exists()) {
            $find = static::create(['SITE_KEY' => $site_key]);
        }

        if ($host && $host !== (string) $find->SITE_HOST) {
            $find->SITE_HOST = $host;
            $find->write();
        }

        return $find;
    }

    /**
     * @return App
     */
    public static function app(): App
    {
        if (is_null(self::$_app)) {
            self::$_app = new App(
                (string) self::config()->get('APP_ID'),
                (string) self::config()->get('APP_SECRET')
            );
        }

        return self::$_app;
    }
}