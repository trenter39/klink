<?php

    use PHPUnit\Framework\TestCase;

    class DatabaseOperationsTest extends TestCase {
        
        private mysqli $conn;
        private $testDatabaseName = 'test_klink_db';

        protected function setUp(): void {
            $host = getenv("DB_HOST") ?: 'localhost';
            $user = getenv("DB_USER") ?: 'root';
            $pass = getenv("DB_PASS") ?: '';
            
            $this->conn = new mysqli($host, $user, $pass);
            
            if ($this->conn->connect_error) {
                $this->markTestSkipped('Database connection failed: ' . $this->conn->connect_error);
            }
            
            $this->conn->query("drop database if exists {$this->testDatabaseName}");
            $this->conn->query("CREATE DATABASE {$this->testDatabaseName}");
            $this->conn->select_db($this->testDatabaseName);
            
            $this->setupTables();
        }

        protected function tearDown(): void {
            $this->conn->query("drop database if exists {$this->testDatabaseName}");
            $this->conn->close();
        }

        private function setupTables(): void {
            $this->conn->query("
                create table users (
                    id int auto_increment primary key,
                    username varchar(50) unique not null,
                    password varchar(255) not null,
                    role varchar(20) not null,
                    full_name varchar(100),
                    department varchar(100),
                    created_at timestamp default current_timestamp 
                )");
            
            $this->conn->query("
                create table tasks (
                    id int auto_increment primary key,
                    title varchar(255) not null,
                    description text,
                    start_date date,
                    status varchar(50),
                    user_id int not null,
                    created_at timestamp default current_timestamp,
                    foreign key (user_id) references users(id) on delete cascade
                )
            ");
        }

        public function testInsertValidUser() {
            $stmt = $this->conn->prepare("insert into users (username, password, role, full_name, department) values (?, ?, ?, ?, ?)");
            
            $username = 'testuser';
            $password = password_hash('test123', PASSWORD_DEFAULT);
            $role = 'employee';
            $fullName = 'Test User';
            $department = 'IT';
            
            $stmt->bind_param('sssss', $username, $password, $role, $fullName, $department);
            $result = $stmt->execute();
            
            $this->assertTrue($result);
            $this->assertGreaterThan(0, $this->conn->insert_id);
        }

        public function testInsertDuplicateUsername() {
            $stmt = $this->conn->prepare("insert into users (username, password, role, full_name, department) values (?, ?, ?, ?, ?)");
            
            $username = 'duplicate';
            $password = password_hash('test123', PASSWORD_DEFAULT);
            $role = 'employee';
            $fullName = 'Test User';
            $department = 'IT';
            
            $stmt->bind_param('sssss', $username, $password, $role, $fullName, $department);
            $stmt->execute();

            try {
                $stmt->bind_param('sssss', $username, $password, $role, $fullName, $department);
                $stmt->execute();
                $this->fail('Expected duplicate entry error was not thrown');
            } catch (\mysqli_sql_exception $e) {
                $this->assertStringContainsString('Duplicate entry', $e->getMessage());
            }
        }

        public function testFetchUserByUsername() {
            $stmt = $this->conn->prepare("insert into users (username, password, role, full_name) values (?, ?, ?, ?)");
            $username = 'john';
            $password = 'hash123';
            $role = 'admin';
            $fullName = 'John Doe';
            $stmt->bind_param('ssss', $username, $password, $role, $fullName);
            $stmt->execute();
            
            $selectStmt = $this->conn->prepare("select id, username, role, full_name from users where username = ?");
            $selectStmt->bind_param('s', $username);
            $selectStmt->execute();
            $result = $selectStmt->get_result();
            $user = $result->fetch_assoc();
            
            $this->assertNotNull($user);
            $this->assertEquals('john', $user['username']);
            $this->assertEquals('admin', $user['role']);
        }

        public function testUpdateUserRole() {
            $stmt = $this->conn->prepare("insert into users (username, password, role, full_name) values (?, ?, ?, ?)");
            $username = 'jane';
            $password = 'hash456';
            $role = 'employee';
            $fullName = 'Jane Smith';
            $stmt->bind_param('ssss', $username, $password, $role, $fullName);
            $stmt->execute();
            $userId = $this->conn->insert_id;
            
            $updateStmt = $this->conn->prepare("update users set role = ? where id = ?");
            $newRole = 'admin';
            $updateStmt->bind_param('si', $newRole, $userId);
            $updateResult = $updateStmt->execute();
            
            $this->assertTrue($updateResult);
            
            $selectStmt = $this->conn->prepare("select role from users where id = ?");
            $selectStmt->bind_param('i', $userId);
            $selectStmt->execute();
            $result = $selectStmt->get_result();
            $user = $result->fetch_assoc();
            
            $this->assertEquals('admin', $user['role']);
        }

        public function testInsertTaskForUser() {
            $stmt = $this->conn->prepare("insert into users (username, password, role, full_name) values (?, ?, ?, ?)");
            $username = 'worker';
            $password = 'hash789';
            $role = 'employee';
            $fullName = 'Worker Name';
            $stmt->bind_param('ssss', $username, $password, $role, $fullName);
            $stmt->execute();
            $userId = $this->conn->insert_id;
            
            $taskStmt = $this->conn->prepare("insert into tasks (title, description, start_date, status, user_id) values (?, ?, ?, ?, ?)");
            $title = 'Test Task';
            $description = 'Task description';
            $startDate = '2024-01-30';
            $status = 'New';
            
            $taskStmt->bind_param('ssssi', $title, $description, $startDate, $status, $userId);
            $result = $taskStmt->execute();
            
            $this->assertTrue($result);
        }

        public function testFetchTasksForUser() {
            $stmt = $this->conn->prepare("insert into users (username, password, role, full_name) values (?, ?, ?, ?)");
            $username = 'alice';
            $password = 'hash111';
            $role = 'employee';
            $fullName = 'Alice';
            $stmt->bind_param('ssss', $username, $password, $role, $fullName);
            $stmt->execute();
            $userId = $this->conn->insert_id;
            
            $taskStmt = $this->conn->prepare("insert into tasks (title, start_date, status, user_id) values (?, ?, ?, ?)");
            for ($i = 1; $i <= 3; $i++) {
                $title = "Task $i";
                $startDate = '2024-01-30';
                $status = 'New';
                $taskStmt->bind_param('sssi', $title, $startDate, $status, $userId);
                $taskStmt->execute();
            }
            
            $selectStmt = $this->conn->prepare("select * from tasks where user_id = ? order by id");
            $selectStmt->bind_param('i', $userId);
            $selectStmt->execute();
            $result = $selectStmt->get_result();
            
            $this->assertEquals(3, $result->num_rows);
        }

        public function testDeleteTask() {
            $stmt = $this->conn->prepare("insert into users (username, password, role, full_name) values (?, ?, ?, ?)");
            $username = 'bob';
            $password = 'hash222';
            $role = 'employee';
            $fullName = 'Bob';
            $stmt->bind_param('ssss', $username, $password, $role, $fullName);
            $stmt->execute();
            $userId = $this->conn->insert_id;
            
            $taskStmt = $this->conn->prepare("insert into tasks (title, status, user_id) values (?, ?, ?)");
            $title = 'Task to delete';
            $status = 'New';
            $taskStmt->bind_param('ssi', $title, $status, $userId);
            $taskStmt->execute();
            $taskId = $this->conn->insert_id;
            
            $deleteStmt = $this->conn->prepare("delete from tasks where id = ?");
            $deleteStmt->bind_param('i', $taskId);
            $result = $deleteStmt->execute();
            
            $this->assertTrue($result);
            
            $checkStmt = $this->conn->prepare("select id from tasks where id = ?");
            $checkStmt->bind_param('i', $taskId);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            $this->assertEquals(0, $checkResult->num_rows);
        }

        public function testSearchUserByName() {
            $stmt = $this->conn->prepare("insert into users (username, password, role, full_name) values (?, ?, ?, ?)");
            $names = ['John Smith', 'Jane Smith', 'Alice Johnson', 'Bob Wilson'];
            $password = 'hash';
            $role = 'employee';
            
            foreach ($names as $name) {
                $username = strtolower(str_replace(' ', '_', $name));
                $stmt->bind_param('ssss', $username, $password, $role, $name);
                $stmt->execute();
            }
            
            $search = "%Smith%";
            $searchStmt = $this->conn->prepare("select * from users where full_name like ? order by full_name");
            $searchStmt->bind_param('s', $search);
            $searchStmt->execute();
            $result = $searchStmt->get_result();
            
            $this->assertEquals(2, $result->num_rows);
        }

        public function testUpdateTaskStatus() {
            $stmt = $this->conn->prepare("insert into users (username, password, role, full_name) values (?, ?, ?, ?)");
            $username = 'charlie';
            $password = 'hash333';
            $role = 'employee';
            $fullName = 'Charlie';
            $stmt->bind_param('ssss', $username, $password, $role, $fullName);
            $stmt->execute();
            $userId = $this->conn->insert_id;
            
            $taskStmt = $this->conn->prepare("insert into tasks (title, status, user_id) values (?, ?, ?)");
            $title = 'Task';
            $status = 'New';
            $taskStmt->bind_param('ssi', $title, $status, $userId);
            $taskStmt->execute();
            $taskId = $this->conn->insert_id;
            
            $newStatus = 'In Progress';
            $updateStmt = $this->conn->prepare("update tasks set status = ? where id = ?");
            $updateStmt->bind_param('si', $newStatus, $taskId);
            $updateResult = $updateStmt->execute();
            
            $this->assertTrue($updateResult);
            
            $checkStmt = $this->conn->prepare("select status from tasks where id = ?");
            $checkStmt->bind_param('i', $taskId);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            $task = $result->fetch_assoc();
            
            $this->assertEquals('In Progress', $task['status']);
        }
    }
?>
