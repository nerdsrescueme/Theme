<?php

namespace Theme\Tests;

class TemplateNotFoundTest extends \PHPUnit_Framework_TestCase
{
    protected $ref;

    public function setUp()
    {
        $this->ref = new \ReflectionClass('\\Theme\\Exception\\TemplateNotFound');
    }

    /**
     * @covers \Nerd\Exception
     */
    public function testExceptionInThemeNamespace()
    {
        $this->assertEquals($this->ref->getNamespaceName(), 'Theme\\Exception');
    }

    /**
     * @covers \Nerd\Exception
     */
    public function testExceptionExtendsException()
    {
        $exception = $this->ref->newInstance('Test message');
        $this->assertTrue($exception instanceof \Exception);
    }

    /**
     * @covers \Nerd\Exception
     */
    public function testExceptionExtendsThemeException()
    {
        $exception = $this->ref->newInstance('Test message');
        $this->assertTrue($exception instanceof \Theme\Exception);
    }

    /**
     * @covers \Nerd\Exception
     * @expectedException \Theme\Exception\TemplateNotFound
     * @expectedExceptionMessage Test
     */
    public function testExceptionCanBeThrown()
    {
        throw new \Theme\Exception\TemplateNotFound('Test');
    }
}
