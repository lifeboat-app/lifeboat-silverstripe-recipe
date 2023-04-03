<?php

namespace Lifeboat\Controllers;

use Lifeboat\Exceptions\OAuthException;
use Lifeboat\Exceptions\RuntimeException;
use Lifeboat\Models\Site;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Config\Config;
use SilverStripe\View\TemplateGlobalProvider;

/**
 * Class Auth
 *
 * @package Lifeboat\Controllers
 */
class Auth extends Controller implements TemplateGlobalProvider {

    private static $url_segment     = 'lifeboat-auth';
    private static $allowed_actions = ['process', 'error', 'logout'];

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

    public function logout(HTTPRequest $request): HTTPResponse
    {
        return $this->reloadAuth();
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
        try {
            $session = $this->getRequest()->getSession();
            if ($session) $session->clearAll();
        } catch (\Exception $e) {
            error_log($e);
        }

        $process    = Director::absoluteURL($this->Link('process'));
        $error      = Director::absoluteURL($this->Link('error'));
        $challenge  = Site::app()->getAPIChallenge();

        return $this->redirect(Site::app()->getAuthURL($process, $error, $challenge));
    }

    /**
     * @return string
     */
    public static function logout_url(): string
    {
        return Director::absoluteURL(
            Controller::join_links(
                Config::inst()->get(self::class, 'url_segment'),
                'logout'
            )
        );
    }

    /**
     * @return array
     */
    public static function get_template_global_variables(): array
    {
        return [
            'AUTH_LOGOUT_URL' => 'logout_url'
        ];
    }
}
