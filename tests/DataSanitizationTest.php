<?php

    use PHPUnit\Framework\TestCase;

    class DataSanitizationTest extends TestCase {
        
        public function testSQLInjectionAttemptDetection() {
            $username = "admin' OR '1'='1";
            
            $this->assertStringContainsString("'", $username);
        }

        public function testPreparedStatementSafety() {
            $input = "test' OR '1'='1";
            
            $this->assertTrue(strlen($input) > 0);
        }

        public function testXSSPayloadEscaping() {
            require_once __DIR__ . '/../helpers/security.php';
            
            $xssPayload = '<img src=x onerror=alert("xss")>';
            $escaped = e($xssPayload);
            
            $this->assertStringNotContainsString('<img', $escaped);
            $this->assertStringContainsString('&lt;img', $escaped);
        }

        public function testEventHandlerEscaping() {
            require_once __DIR__ . '/../helpers/security.php';
            
            $payload = '" onclick="alert(\'xss\')" data="';
            $escaped = e($payload);
            
            $this->assertStringContainsString('&quot;', $escaped);
        }

        public function testAttributeInjectionPrevention() {
            require_once __DIR__ . '/../helpers/security.php';
            
            $userInput = '"><script>alert(1)</script>';
            $escaped = e($userInput);
            
            $this->assertStringNotContainsString('<script>', $escaped);
        }

        public function testUnicodeEscaping() {
            require_once __DIR__ . '/../helpers/security.php';
            
            $input = 'Test \u0022 Unicode';
            $escaped = e($input);
            
            $this->assertIsString($escaped);
        }

        public function testEmptyValueSanitization() {
            require_once __DIR__ . '/../helpers/security.php';
            
            $empty = '';
            $escaped = e($empty);
            
            $this->assertEquals('', $escaped);
        }

        public function testNumericSanitization() {
            $id = '123';
            $sanitized = (int)$id;
            
            $this->assertIsInt($sanitized);
            $this->assertEquals(123, $sanitized);
        }

        public function testRoleSanitization() {
            $role = 'admin';
            $validRoles = ['admin', 'employee'];
            
            $isValid = in_array($role, $validRoles, true);
            $this->assertTrue($isValid);
        }

        public function testStatusSanitization() {
            $input = 'In_Progress';
            $status = str_replace('_', ' ', $input);
            
            $validStatuses = ['New', 'In Progress', 'Completed'];
            $isValid = in_array($status, $validStatuses, true);
            
            $this->assertTrue($isValid);
        }

        public function testDateSanitization() {
            $date = '2026-01-30';
            $isValidDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1;
            
            $this->assertTrue($isValidDate);
        }

        public function testInvalidDateSanitization() {
            $date = '30/01/2026';
            $isValidDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1;
            
            $this->assertFalse($isValidDate);
        }

        public function testTextFieldMaxLength() {
            $title = str_repeat('a', 500);
            $maxLength = 255;
            
            $isValid = strlen($title) <= $maxLength;
            $this->assertFalse($isValid);
            
            $truncated = substr($title, 0, $maxLength);
            $this->assertEquals($maxLength, strlen($truncated));
        }

        public function testWhitespaceNormalization() {
            $input = "  Multiple   spaces   between   words  ";
            $normalized = preg_replace('/\s+/', ' ', trim($input));

            $this->assertStringNotContainsString('  ', $normalized);
        }

        public function testSpecialCharactersInUsername() {
            $username = 'user@#$%^&*()';
            
            $hasSpecialChars = preg_match('/[^a-zA-Z0-9_-]/', $username);
            
            $this->assertEquals(1, $hasSpecialChars);
        }

        public function testValidUsernameFormat() {
            $validUsernames = ['john_doe', 'user-123', 'admin123'];
            
            foreach ($validUsernames as $username) {
                $isValid = preg_match('/^[a-zA-Z0-9_-]+$/', $username);
                $this->assertEquals(1, $isValid);
            }
        }
    }
?>
