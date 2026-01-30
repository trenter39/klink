<?php

    use PHPUnit\Framework\TestCase;

    class CsrfTokenTest extends TestCase {
        
        protected function setUp(): void {
            if (!headers_sent()) {
                    if (session_status() !== PHP_SESSION_ACTIVE) {
                        session_start();
                    }
                }
            $_SESSION = [];
        }

        public function testCsrfTokenGeneration() {
            require_once __DIR__ . '/../helpers/csrf.php';
            
            $token1 = csrf_token();
            $this->assertNotEmpty($token1);
            $this->assertEquals(64, strlen($token1));
        }

        public function testCsrfTokenConsistency() {
            require_once __DIR__ . '/../helpers/csrf.php';
            
            $token1 = csrf_token();
            $token2 = csrf_token();
            
            $this->assertEquals($token1, $token2);
        }

        public function testCsrfTokenSessionStorage() {
            require_once __DIR__ . '/../helpers/csrf.php';
            
            csrf_token();
            $this->assertNotEmpty($_SESSION['csrf_token']);
            $this->assertEquals(64, strlen($_SESSION['csrf_token']));
        }

        public function testCsrfTokenIsHexadecimal() {
            require_once __DIR__ . '/../helpers/csrf.php';
            
            $token = csrf_token();
            $this->assertTrue(ctype_xdigit($token));
        }

        public function testCsrfCheckPassesWithValidToken() {
            require_once __DIR__ . '/../helpers/csrf.php';
            
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $token = csrf_token();
            $_POST['csrf_token'] = $token;
            $_SESSION['csrf_token'] = $token;
            
            try {
                csrf_check();
                $this->assertTrue(true);
            } catch (\Exception $e) {
                $this->fail('csrf_check should not throw exception with valid token');
            }
        }

        /** @runInSeparateProcess */
        public function testCsrfCheckFailsWithInvalidToken() {
            require_once __DIR__ . '/../helpers/csrf.php';
            
            $_SERVER['REQUEST_METHOD'] = 'POST';
            csrf_token();
            $_POST['csrf_token'] = 'invalid_token_12345';
            
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('Invalid CSRF token');
            csrf_check();
        }

        /** @runInSeparateProcess */

        public function testCsrfCheckFailsWithMissingToken() {
            require_once __DIR__ . '/../helpers/csrf.php';
            
            $_SERVER['REQUEST_METHOD'] = 'POST';
            csrf_token();
            $_POST = [];
            
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('Invalid CSRF token');
            csrf_check();
        }

        public function testCsrfCheckBypassOnGetRequest() {
            require_once __DIR__ . '/../helpers/csrf.php';
            
            $_SERVER['REQUEST_METHOD'] = 'GET';
            $_POST = [];
            
            try {
                csrf_check();
                $this->assertTrue(true);
            } catch (\Exception $e) {
                $this->fail('csrf_check should pass for GET requests');
            }
        }

        /** @runInSeparateProcess */
        public function testCsrfCheckWithEmptySessionToken() {
            require_once __DIR__ . '/../helpers/csrf.php';
            
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST['csrf_token'] = 'some_token';
            $_SESSION['csrf_token'] = '';
            
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('Invalid CSRF token');
            csrf_check();
        }
    }
?>
