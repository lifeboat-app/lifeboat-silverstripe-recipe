<?php

namespace Lifeboat\Extensions;

use Lifeboat\Exceptions\LogicException;
use Lifeboat\Models\Site;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataQuery;
use SilverStripe\ORM\Queries\SQLSelect;

/**
 * Class SiteObject
 *
 * Apply this extension to your objects to automatically filter
 * objects for the currently active site
 *
 * @package Lifeboat\Extensions
 *
 * @method Site|null Site()
 */
class SiteObject extends DataExtension {

    private static $has_one = [
        'Site' => Site::class
    ];

    public function augmentSQL(SQLSelect $query, DataQuery $dataQuery = null)
    {
        $site = Site::curr();
        if (!$site) throw new LogicException("Cannot retrieve SiteObjects without an active site. Are you logged in?");

        $query->addWhere("SiteID = {$site->ID}");
        $dataQuery->where("SiteID = {$site->ID}");
    }

}