<?php

namespace Lifeboat\Controllers;

use Lifeboat\Exceptions\OAuthException;
use Lifeboat\Exceptions\RuntimeException;
use Lifeboat\Models\Site;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;

/**
 * Class Auth
 *
 * @package Lifeboat\Controllers
 */
class Auth extends Controller {

    private static $url_segment     = 'lifeboat-auth';
    private static $allowed_actions = ['process', 'error'];

    /**
     * @param HTTPRequest $request
     * @return HTTPResponse
     * @throws RuntimeException If sessions are not enabled
     */
    public function handleRequest(HTTPRequest $request)
    {
        // Make sure we have sessions running
        if (!$request->getSession()->isStarted()) {
            $request->getSession()->start($request);
        }

        if (!$request->getSession()->isStarted()) {
            throw new RuntimeException("Sessions have to be enabled to use the Lifeboat SDK");
        }

        return parent::handleRequest($request);
    }

    /**
     * @param HTTPRequest $request
     * @return HTTPResponse
     */
    public function process(HTTPRequest $request): HTTPResponse
    {
        try {
            // Set all the session data
            Site::app()->fetchAccessToken($request->getVar('code'));

            // Save the current site object
            Site::curr();
        } catch (OAuthException $e) {
            error_log($e);
            return $this->reloadAuth();
        }

        if (!Site::app()->getActiveSite()) {
            return $this->reloadAuth();
        }

        return $this->redirect('/');
    }

    /**
     * @return HTTPResponse
     */
    public function error(): HTTPResponse
    {
        return $this->reloadAuth();
    }

    /**
     * @return HTTPResponse
     */
    public function reloadAuth(): HTTPResponse
    {
        $process    = Director::absoluteURL($this->Link('process'));
        $error      = Director::absoluteURL($this->Link('error'));
        $challenge  = Site::app()->getAPIChallenge();

        return $this->redirect(Site::app()->getAuthURL($process, $error, $challenge));
    }

}