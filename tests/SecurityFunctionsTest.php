<?php

    use PHPUnit\Framework\TestCase;

    class SecurityFunctionsTest extends TestCase {
        
        public function testEscapingSimpleString() {
            require_once __DIR__ . '/../public/helpers/security.php';
            
            $input = '<script>alert("xss")</script>';
            $output = e($input);
            
            $this->assertStringNotContainsString('<script>', $output);
            $this->assertStringContainsString('&lt;script&gt;', $output);
        }

        public function testEscapingHtmlEntities() {
            require_once __DIR__ . '/../public/helpers/security.php';
            
            $input = '"double quotes"';
            $output = e($input);
            
            $this->assertStringContainsString('&quot;', $output);
        }

        public function testEscapingSingleQuotes() {
            require_once __DIR__ . '/../public/helpers/security.php';
            
            $input = "'single quotes'";
            $output = e($input);
            
            $this->assertStringNotContainsString("'", $output);
        }

        public function testEscapingAmpersand() {
            require_once __DIR__ . '/../public/helpers/security.php';
            
            $input = 'Jack & Jill';
            $output = e($input);
            
            $this->assertStringContainsString('&amp;', $output);
        }

        public function testEscapingPreservesContent() {
            require_once __DIR__ . '/../public/helpers/security.php';
            
            $input = 'Hello World';
            $output = e($input);
            
            $this->assertEquals('Hello World', $output);
        }

        public function testEscapingIntegerInput() {
            require_once __DIR__ . '/../public/helpers/security.php';
            
            $input = 12345;
            $output = e($input);
            
            $this->assertEquals('12345', $output);
        }

        public function testEscapingNullInput() {
            require_once __DIR__ . '/../public/helpers/security.php';
            
            $input = null;
            $output = e($input);
            
            $this->assertEquals('', $output);
        }

        public function testEscapingComplexHtmlInjection() {
            require_once __DIR__ . '/../public/helpers/security.php';
            
            $input = '<img src=x onerror="alert(\'xss\')" />';
            $output = e($input);
            
            $this->assertStringContainsString('&lt;img', $output);
            $this->assertStringContainsString('&quot;', $output);
        }

        public function testEscapingMultilineContent() {
            require_once __DIR__ . '/../public/helpers/security.php';
            
            $input = "Line 1\nLine 2\n<script>alert('xss')</script>";
            $output = e($input);
            
            $this->assertStringContainsString('Line 1', $output);
            $this->assertStringContainsString('Line 2', $output);
            $this->assertStringNotContainsString('<script>', $output);
        }
    }
?>
