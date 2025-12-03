<?php

require_once __DIR__ . '/../presentation/smarty_plugins/01.modifier.prepare_link.php'; // NOSONAR - Legacy PHP application without PSR-4 autoloading
use PHPUnit\Framework\TestCase;


class LinkModifierTest extends TestCase {
    private const TEST_PATH = 'path/to/file';
    private const BASE_LINK = 'https://example.com';
    private const INDEX_PHP = 'index.php';
    private const ADMIN_PHP = 'admin.php';
    private const SOME_PATH = 'some/path';

    // Test joinPaths function
    public function testJoinPaths() {
        $result = joinPaths('path1', 'path2', 'path3');
        $this->assertEquals('path1' . DIRECTORY_SEPARATOR . 'path2' . DIRECTORY_SEPARATOR . 'path3', $result);
    }

    // Test trimPath function
    public function testTrimPath() {
        $result = trimPath('/path/to/file/');
        $this->assertEquals(self::TEST_PATH, $result);

        $result = trimPath(self::TEST_PATH);
        $this->assertEquals(self::TEST_PATH, $result);
    }

    // Test generateBaseLink function
    public function testGenerateBaseLink() {
        putenv('SERVER_NAME=example.com');
        $result = generateBaseLink();
        $this->assertEquals(self::BASE_LINK, $result);
    }

    // Test appendPathToLink function
    public function testAppendPathToLink() {
        $baseLink = self::BASE_LINK;
        $string = self::SOME_PATH;
        $result = appendPathToLink($baseLink, $string);
        $this->assertEquals(self::BASE_LINK . DIRECTORY_SEPARATOR . 'some' . DIRECTORY_SEPARATOR . 'path', $result);
    }

    // Test needsIndexPage function
    public function testNeedsIndexPage() {
        $linkWithIndex = self::BASE_LINK . '/' . self::INDEX_PHP;
        $linkWithAdmin = self::BASE_LINK . '/' . self::ADMIN_PHP;
        $linkWithoutIndex = self::BASE_LINK . '/' . self::SOME_PATH;

        $this->assertFalse(needsIndexPage($linkWithIndex));  // Should return false
        $this->assertFalse(needsIndexPage($linkWithAdmin));  // Should return false
        $this->assertTrue(needsIndexPage($linkWithoutIndex));  // Should return true
    }

    // Test appendIndexIfNeeded function
    public function testAppendIndexIfNeeded() {
        $linkWithoutIndex = self::BASE_LINK . '/' . self::SOME_PATH;
        $result = appendIndexIfNeeded($linkWithoutIndex);
        $this->assertEquals(self::BASE_LINK . '/' . self::SOME_PATH . '/' . self::INDEX_PHP, $result);

        $linkWithIndex = self::BASE_LINK . '/' . self::INDEX_PHP;
        $result = appendIndexIfNeeded($linkWithIndex);
        $this->assertEquals(self::BASE_LINK . '/' . self::INDEX_PHP, $result);
    }

    // Test escapeUrl function
    public function testEscapeUrl() {
        $unsafeUrl = self::BASE_LINK . '/?<script>alert("XSS")</script>';
        $result = escapeUrl($unsafeUrl);
        $this->assertEquals(self::BASE_LINK . '/?&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;', $result);
    }

    // Test smarty_modifier_prepare_link function
    public function testSmartyModifierPrepareLink() {
        putenv('SERVER_NAME=example.com');

        // Case with path needing index.php
        $result = smarty_modifier_prepare_link(self::SOME_PATH);
        $this->assertEquals(self::BASE_LINK . '/' . self::SOME_PATH . '/' . self::INDEX_PHP, $result);

        // Case with path already containing index.php
        $result = smarty_modifier_prepare_link(self::SOME_PATH . '/' . self::INDEX_PHP);
        $this->assertEquals(self::BASE_LINK . '/' . self::SOME_PATH . '/' . self::INDEX_PHP, $result);

        // Case with path containing admin.php
        $result = smarty_modifier_prepare_link(self::SOME_PATH . '/' . self::ADMIN_PHP);
        $this->assertEquals(self::BASE_LINK . '/' . self::SOME_PATH . '/' . self::ADMIN_PHP, $result);
    }
}
