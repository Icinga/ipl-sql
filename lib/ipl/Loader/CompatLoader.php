<?php

namespace ipl\Loader;

use Icinga\Application\ApplicationBootstrap;

/**
 * This loader delegates auto-loading to the legacy Icinga Web 2 loader and
 * therefore requires an initialized Icinga\Application.
 */
class CompatLoader
{
    /**
     * @param ApplicationBootstrap $app
     */
    public static function delegateLoadingToIcingaWeb(ApplicationBootstrap $app)
    {
        $app->getLoader()->registerNamespace(
            'ipl',
            dirname(__DIR__)
        );
    }
}
