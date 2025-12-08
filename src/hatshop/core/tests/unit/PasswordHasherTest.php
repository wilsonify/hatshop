<?php

namespace Hatshop\Core\Tests\Unit;

use Hatshop\Core\PasswordHasher;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for PasswordHasher class.
 */
class PasswordHasherTest extends TestCase
{
    protected function setUp(): void
    {
        // Reset the prefix before each test
        PasswordHasher::setPrefix('');
    }

    protected function tearDown(): void
    {
        // Clean up
        PasswordHasher::setPrefix('');
    }

    public function testHashWithoutPrefix(): void
    {
        $password = 'testpassword123';
        $hash = PasswordHasher::hash($password, false);
        
        $this->assertEquals(hash('sha256', $password), $hash);
    }

    public function testHashWithEmptyPrefix(): void
    {
        $password = 'testpassword123';
        $hash = PasswordHasher::hash($password, true);
        
        // With empty prefix, result should be same as without prefix
        $this->assertEquals(hash('sha256', $password), $hash);
    }

    public function testHashWithPrefix(): void
    {
        $prefix = 'secretprefix';
        $password = 'testpassword123';
        
        PasswordHasher::setPrefix($prefix);
        $hash = PasswordHasher::hash($password, true);
        
        $this->assertEquals(hash('sha256', $prefix . $password), $hash);
    }

    public function testVerifyCorrectPassword(): void
    {
        $password = 'testpassword123';
        $hash = PasswordHasher::hash($password, false);
        
        $this->assertTrue(PasswordHasher::verify($password, $hash, false));
    }

    public function testVerifyIncorrectPassword(): void
    {
        $password = 'testpassword123';
        $wrongPassword = 'wrongpassword';
        $hash = PasswordHasher::hash($password, false);
        
        $this->assertFalse(PasswordHasher::verify($wrongPassword, $hash, false));
    }

    public function testVerifyWithPrefix(): void
    {
        $prefix = 'secretprefix';
        $password = 'testpassword123';
        
        PasswordHasher::setPrefix($prefix);
        $hash = PasswordHasher::hash($password, true);
        
        $this->assertTrue(PasswordHasher::verify($password, $hash, true));
    }

    public function testVerifyWithWrongPrefixSetting(): void
    {
        $prefix = 'secretprefix';
        $password = 'testpassword123';
        
        PasswordHasher::setPrefix($prefix);
        $hash = PasswordHasher::hash($password, true);
        
        // Verify with prefix=false should fail
        $this->assertFalse(PasswordHasher::verify($password, $hash, false));
    }

    public function testGetPrefix(): void
    {
        $prefix = 'myprefix';
        PasswordHasher::setPrefix($prefix);
        
        $this->assertEquals($prefix, PasswordHasher::getPrefix());
    }

    public function testSetPrefix(): void
    {
        $this->assertEquals('', PasswordHasher::getPrefix());
        
        PasswordHasher::setPrefix('newprefix');
        
        $this->assertEquals('newprefix', PasswordHasher::getPrefix());
    }

    public function testHashIsDeterministic(): void
    {
        $password = 'testpassword123';
        $hash1 = PasswordHasher::hash($password, false);
        $hash2 = PasswordHasher::hash($password, false);
        
        $this->assertEquals($hash1, $hash2);
    }

    public function testDifferentPasswordsProduceDifferentHashes(): void
    {
        $password1 = 'password1';
        $password2 = 'password2';
        
        $hash1 = PasswordHasher::hash($password1, false);
        $hash2 = PasswordHasher::hash($password2, false);
        
        $this->assertNotEquals($hash1, $hash2);
    }

    public function testHashLength(): void
    {
        $password = 'testpassword123';
        $hash = PasswordHasher::hash($password, false);
        
        // SHA1 produces 40 character hex string
        $this->assertEquals(40, strlen($hash));
    }
}
