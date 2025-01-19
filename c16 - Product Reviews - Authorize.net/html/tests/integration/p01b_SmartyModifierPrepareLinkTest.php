<?php

require_once __DIR__ . '/../../presentation/smarty_plugins/01.modifier.prepare_link.php';
use PHPUnit\Framework\TestCase;


class p01b_SmartyModifierPrepareLinkTest extends TestCase
{
    protected function setUp(): void
    {
        putenv('SERVER_NAME=testserver.com');
    }

    public function testPrepareLinkWithIndexPhp()
    {
        $result = smarty_modifier_prepare_link('index.php');
        $expected = 'https://testserver.com/index.php';
        $this->assertEquals($expected, $result);
    }

    public function testPrepareLinkWithAdminPhp()
    {
        $result = smarty_modifier_prepare_link('admin.php');
        $expected = 'https://testserver.com/admin.php';
        $this->assertEquals($expected, $result);
    }

    public function testPrepareLinkWithoutIndexOrAdmin()
    {
        $result = smarty_modifier_prepare_link('products');
        $expected = 'https://testserver.com/products/index.php';
        $this->assertEquals($expected, $result);
    }

    public function testPrepareLinkWithLeadingAndTrailingSlashes()
    {
        $result = smarty_modifier_prepare_link('/products/');
        $expected = 'https://testserver.com/products/index.php';
        $this->assertEquals($expected, $result);
    }

    public function testPrepareLinkWithSpecialCharacters()
    {
        $result = smarty_modifier_prepare_link('products?name=<script>alert("xss")</script>');
        $expected = 'https://testserver.com/products/index.php?name=&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;';
        $this->assertEquals($expected, $result);
    }
}
