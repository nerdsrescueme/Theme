<?php

namespace Theme\Tests;

class ThemeTest extends \PHPUnit_Framework_TestCase
{
    protected $ref;

    public function setUp()
    {
        $this->ref = new \ReflectionClass('\\Theme\\Theme');
    }

    /**
     * @covers \Nerd\Theme
     */
    public function testThemeInThemeNamespace()
    {
       $this->assertEquals($this->ref->getNamespaceName(), 'Theme');
    }

    // ... need more
}
