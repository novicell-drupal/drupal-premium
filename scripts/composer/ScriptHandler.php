<?php
namespace Premium\scripts;

use Composer\Script\Event;

class ScriptHandler {
    /**
     * Pre install premium.
     *
     * @param \Composer\Script\Event $event
     *   Composer event.
     */
    public static function postRootPackageInstall(Event $event) {
        $information = [];
        if (!empty($domain_name = $event->getIO()->ask('Domain name:'))) {
            $information['domain_name'] = $domain_name;
        }
        if (!empty($project_name = $event->getIO()->ask('Project name:'))) {
            $information['project_name'] = $project_name;
        }
        if ($values = $event->getIO()->select('Optional modules', ['Cookiebot', 'GTM', 'Media bulk upload'], 'none', FALSE, 'Value "%s" is invalid', TRUE)) {
            if (is_array($values)) {
                $information['values'] = implode(', ', $values);
            }
        }
        var_dump($information);
    }
}