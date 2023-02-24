<?php

namespace Lifeboat\Controllers;

use Lifeboat\Models\Site;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;

/**
 * Class AppController
 *
 * A middleware controller that will ensure anyone that interacts
 * with your app is authenticated with the Lifeboat SDK
 * and will ensure they have permission to access the currently active site
 *
 * @package Lifeboat\Controllers
 */
class AppController extends Controller {

    public function handleRequest(HTTPRequest $request)
    {
        // Make sure to start the sessions first
        if (!$request->getSession()->isStarted()) {
            $request->getSession()->start($request);
        }

        if (!Site::curr()) {
            $auth = new Auth();
            return $auth->reloadAuth();
        }

        // This ensures the user is logged in & current token is valid
        try {
            Site::app()->getSites();
        } catch (OAuthException $e) {
            $auth = new Auth();
            return $auth->reloadAuth();
        }
        
        return parent::handleRequest($request);
    }

}
