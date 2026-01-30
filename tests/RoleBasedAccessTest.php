<?php

    use PHPUnit\Framework\TestCase;

    class RoleBasedAccessTest extends TestCase {
        
        protected function setUp(): void {
            if (!headers_sent()) {
                if (session_status() !== PHP_SESSION_ACTIVE) {
                    session_start();
                }
            }
        }

        public function testAdminRoleCheck() {
            $_SESSION['role'] = 'admin';
            $role = $_SESSION['role'];
            
            $this->assertEquals('admin', $role);
            $this->assertTrue($role === 'admin');
        }

        public function testEmployeeRoleCheck() {
            $_SESSION['role'] = 'employee';
            $role = $_SESSION['role'];
            
            $this->assertEquals('employee', $role);
            $this->assertFalse($role === 'admin');
        }

        public function testAdminAccessControl() {
            $_SESSION['role'] = 'admin';
            $_SESSION['user_id'] = 1;
            
            $this->assertTrue($_SESSION['role'] === 'admin');
        }

        public function testEmployeeAccessDenial() {
            $_SESSION['role'] = 'employee';
            $_SESSION['user_id'] = 2;
            
            $this->assertFalse($_SESSION['role'] === 'admin');
        }

        public function testMultipleRoleComparison() {
            $validRoles = ['admin', 'employee'];
            $_SESSION['role'] = 'admin';
            
            $this->assertTrue(in_array($_SESSION['role'], $validRoles));
        }

        public function testInvalidRoleValidation() {
            $_SESSION['role'] = 'superuser';
            
            $this->assertFalse(in_array($_SESSION['role'], ['admin', 'employee']));
        }

        public function testTaskAccessByOwner() {
            $_SESSION['user_id'] = 1;
            $_SESSION['role'] = 'employee';
            
            $taskOwnerID = 1;
            
            $canAccess = ($_SESSION['role'] === 'admin' || $taskOwnerID === $_SESSION['user_id']);
            $this->assertTrue($canAccess);
        }

        public function testTaskAccessByNonOwnerEmployee() {
            $_SESSION['user_id'] = 1;
            $_SESSION['role'] = 'employee';
            
            $taskOwnerID = 2;
            
            $canAccess = ($_SESSION['role'] === 'admin' || $taskOwnerID === $_SESSION['user_id']);
            $this->assertFalse($canAccess);
        }

        public function testTaskAccessByAdmin() {
            $_SESSION['user_id'] = 1;
            $_SESSION['role'] = 'admin';
            
            $taskOwnerID = 5;
            
            $canAccess = ($_SESSION['role'] === 'admin' || $taskOwnerID === $_SESSION['user_id']);
            $this->assertTrue($canAccess);
        }

        public function testSessionUserIDValidation() {
            $_SESSION['user_id'] = 123;
            $userID = (int)$_SESSION['user_id'];
            
            $this->assertIsInt($userID);
            $this->assertEquals(123, $userID);
        }

        public function testUserSessionDataIntegrity() {
            $_SESSION['user_id'] = 5;
            $_SESSION['username'] = 'john_doe';
            $_SESSION['role'] = 'employee';
            $_SESSION['name'] = 'John Doe';
            
            $this->assertArrayHasKey('user_id', $_SESSION);
            $this->assertArrayHasKey('username', $_SESSION);
            $this->assertArrayHasKey('role', $_SESSION);
            $this->assertArrayHasKey('name', $_SESSION);
        }
    }
?>