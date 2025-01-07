<?php

require_once __DIR__ . '/../presentation/smarty_plugins/01.modifier.prepare_link.php';
use PHPUnit\Framework\TestCase;


class LinkModifierTest extends TestCase {

    // Test joinPaths function
    public function testJoinPaths() {
        $result = joinPaths('path1', 'path2', 'path3');
        $this->assertEquals('path1' . DIRECTORY_SEPARATOR . 'path2' . DIRECTORY_SEPARATOR . 'path3', $result);
    }

    // Test trimPath function
    public function testTrimPath() {
        $result = trimPath('/path/to/file/');
        $this->assertEquals('path/to/file', $result);

        $result = trimPath('path/to/file');
        $this->assertEquals('path/to/file', $result);
    }

    // Test generateBaseLink function
    public function testGenerateBaseLink() {
        putenv('SERVER_NAME=example.com');
        $result = generateBaseLink('any/path');
        $this->assertEquals('https://example.com', $result);
    }

    // Test appendPathToLink function
    public function testAppendPathToLink() {
        $baseLink = 'https://example.com';
        $string = 'some/path';
        $result = appendPathToLink($baseLink, $string);
        $this->assertEquals('https://example.com' . DIRECTORY_SEPARATOR . 'some' . DIRECTORY_SEPARATOR . 'path', $result);
    }

    // Test needsIndexPage function
    public function testNeedsIndexPage() {
        $linkWithIndex = 'https://example.com/index.php';
        $linkWithAdmin = 'https://example.com/admin.php';
        $linkWithoutIndex = 'https://example.com/some/path';

        $this->assertFalse(needsIndexPage($linkWithIndex));  // Should return false
        $this->assertFalse(needsIndexPage($linkWithAdmin));  // Should return false
        $this->assertTrue(needsIndexPage($linkWithoutIndex));  // Should return true
    }

    // Test appendIndexIfNeeded function
    public function testAppendIndexIfNeeded() {
        $linkWithoutIndex = 'https://example.com/some/path';
        $result = appendIndexIfNeeded($linkWithoutIndex);
        $this->assertEquals('https://example.com/some/path/index.php', $result);

        $linkWithIndex = 'https://example.com/index.php';
        $result = appendIndexIfNeeded($linkWithIndex);
        $this->assertEquals('https://example.com/index.php', $result);
    }

    // Test escapeUrl function
    public function testEscapeUrl() {
        $unsafeUrl = 'https://example.com/?<script>alert("XSS")</script>';
        $result = escapeUrl($unsafeUrl);
        $this->assertEquals('https://example.com/?&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;', $result);
    }

    // Test smarty_modifier_prepare_link function
    public function testSmartyModifierPrepareLink() {
        putenv('SERVER_NAME=example.com');

        // Case with path needing index.php
        $result = smarty_modifier_prepare_link('some/path');
        $this->assertEquals('https://example.com/some/path/index.php', $result);

        // Case with path already containing index.php
        $result = smarty_modifier_prepare_link('some/path/index.php');
        $this->assertEquals('https://example.com/some/path/index.php', $result);

        // Case with path containing admin.php
        $result = smarty_modifier_prepare_link('some/admin.php');
        $this->assertEquals('https://example.com/some/admin.php', $result);
    }
}
