<?php namespace Kloos\Auth;

use Backend;
use System\Classes\PluginBase;

/**
 * Auth Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Auth',
            'description' => 'No description provided yet...',
            'author'      => 'kloos.dev',
            'icon'        => 'icon-leaf'
        ];
    }

    public function registerMailTemplates()
    {
        return [
            'kloos.auth::mail.restore',
        ];
    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
            'Kloos\Auth\Components\ResetPassword' => 'ResetPassword',
        ];
    }
}
