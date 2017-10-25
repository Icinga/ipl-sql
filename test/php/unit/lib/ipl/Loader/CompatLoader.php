<?php

namespace test\ipl\Html;

use Icinga\Application\Version;
use ipl\Loader\CompatLoader;
use ipl\Test\BaseTestCase;

class CompatLoaderTest extends BaseTestCase
{
    public function testIcingaWebClassCanBeLoaded()
    {
        CompatLoader::delegateLoadingToIcingaWeb($this->app());
        $webVersion = Version::get();
        $this->assertTrue(
            version_compare('2.4.0', $webVersion['appVersion'], '<='),
            'Icinga Web version >= 2.4.0 cannot be determined'
        );
    }
}
