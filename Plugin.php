<?php

namespace Kanboard\Plugin\SessionCookieFix;

use Kanboard\Core\Plugin\Base;

/**
 * Removes the session cookie left behind by Kanboard 1.2.54 and older.
 *
 * Up to 1.2.54 the KB_SID cookie was scoped to the directory with a trailing
 * slash ("/kanboard/"). Since 1.2.55 (commit 69ce6fe26) it is scoped to
 * "/kanboard". Browsers that logged in before the upgrade keep both cookies and
 * send the old one first, so PHP reads a stale session id: every request starts
 * a new session and every form fails the CSRF check ("The title is required").
 *
 * Plugins are initialized before the session starts, so the current cookie can
 * still be selected for this request while the browser deletes the old one.
 */
class Plugin extends Base
{
    public function initialize()
    {
        preg_match_all('/(?:^|;)\s*KB_SID=([^;]*)/', $this->request->getServerVariable('HTTP_COOKIE'), $matches);

        if (count($matches[1]) < 2) {
            return;
        }

        $legacyPath = $this->helper->url->dir();

        if ($legacyPath === '/' || substr($legacyPath, -1) !== '/') {
            return;
        }

        setcookie('KB_SID', '', [
            'expires' => 1,
            'path' => $legacyPath,
            'secure' => $this->request->isHTTPS(),
            'httponly' => true,
        ]);

        // Browsers send the cookie with the longest path first: the last one is the current cookie
        $_COOKIE['KB_SID'] = end($matches[1]);
    }

    public function getPluginName()
    {
        return 'SessionCookieFix';
    }

    public function getPluginDescription()
    {
        return 'Removes the session cookie scoped to "/kanboard/" by Kanboard 1.2.54 and older, which breaks forms after upgrading to 1.2.55+';
    }

    public function getPluginAuthor()
    {
        return 'Berwanger Digital';
    }

    public function getPluginVersion()
    {
        return '1.0.0';
    }

    public function getCompatibleVersion()
    {
        return '>=1.2.55';
    }
}
