<?php

    use PHPUnit\Framework\TestCase;

    class PasswordValidationTest extends TestCase {
        
        public function testPasswordHashingWithDefaultAlgorithm() {
            $password = 'securePassword123!';
            $hash = password_hash($password, PASSWORD_DEFAULT);
            
            $this->assertNotEquals($password, $hash);
            $this->assertTrue(password_verify($password, $hash));
        }

        public function testPasswordHashIsDifferentEachTime() {
            $password = 'samePassword';
            $hash1 = password_hash($password, PASSWORD_DEFAULT);
            $hash2 = password_hash($password, PASSWORD_DEFAULT);
            
            $this->assertNotEquals($hash1, $hash2);
            $this->assertTrue(password_verify($password, $hash1));
            $this->assertTrue(password_verify($password, $hash2));
        }

        public function testWrongPasswordVerificationFails() {
            $correctPassword = 'correctPassword';
            $wrongPassword = 'wrongPassword';
            $hash = password_hash($correctPassword, PASSWORD_DEFAULT);
            
            $this->assertFalse(password_verify($wrongPassword, $hash));
        }

        public function testEmptyPasswordHash() {
            $password = '';
            $hash = password_hash($password, PASSWORD_DEFAULT);
            
            $this->assertTrue(password_verify('', $hash));
        }

        public function testPasswordWithSpecialCharacters() {
            $password = 'P@ssw0rd!#$%^&*()_+-={}[]|\\:";\'<>?,./';
            $hash = password_hash($password, PASSWORD_DEFAULT);
            
            $this->assertTrue(password_verify($password, $hash));
        }

        public function testPasswordWithUnicodeCharacters() {
            $password = 'Пароль123!';
            $hash = password_hash($password, PASSWORD_DEFAULT);
            
            $this->assertTrue(password_verify($password, $hash));
        }

        public function testPasswordLengthLimit() {
            $password = str_repeat('a', 1000);
            $hash = password_hash($password, PASSWORD_DEFAULT);
            
            $this->assertTrue(password_verify($password, $hash));
        }

        public function testNeedsRehashDetection() {
            $password = 'test123';
            $hash = password_hash($password, PASSWORD_DEFAULT);
            
            $needsRehash = password_needs_rehash($hash, PASSWORD_DEFAULT);
            $this->assertFalse($needsRehash);
        }

        public function testPasswordHashMinimumLength() {
            $password = 'a';
            $hash = password_hash($password, PASSWORD_DEFAULT);
            
            $this->assertTrue(password_verify($password, $hash));
        }

        public function testCaseSensitivePasswordVerification() {
            $password = 'TestPassword';
            $hash = password_hash($password, PASSWORD_DEFAULT);
            
            $this->assertFalse(password_verify('testpassword', $hash));
            $this->assertFalse(password_verify('TESTPASSWORD', $hash));
            $this->assertTrue(password_verify('TestPassword', $hash));
        }
    }
?>
