<?php

declare(strict_types=1);

namespace Pfinalclub\AsyncioDebug\Tests;

use Pfinalclub\AsyncioDebug\Debug\DebugRuntime;

// Load autoloader
require_once __DIR__ . '/../debug_autoload.php';

// Simple test runner without PHPUnit
function assertTrue(bool $condition, string $message = ''): void
{
    if (!$condition) {
        throw new \Exception($message ?: 'Assertion failed: expected true');
    }
}

function assertFalse(bool $condition, string $message = ''): void
{
    if ($condition) {
        throw new \Exception($message ?: 'Assertion failed: expected false');
    }
}

function assertEquals(mixed $expected, mixed $actual, string $message = ''): void
{
    if ($expected !== $actual) {
        throw new \Exception($message ?: "Assertion failed: expected " . var_export($expected, true) . ", got " . var_export($actual, true));
    }
}

// Test functions
function testDebugRuntimeEnableDisable(): void
{
    $runtime = DebugRuntime::getInstance();
    
    assertFalse($runtime->isEnabled(), 'Runtime should be disabled by default');
    
    $runtime->enable();
    assertTrue($runtime->isEnabled(), 'Runtime should be enabled after calling enable()');
    
    $runtime->disable();
    assertFalse($runtime->isEnabled(), 'Runtime should be disabled after calling disable()');
    
    echo "✓ testDebugRuntimeEnableDisable passed\n";
}

function testDebugRuntimeStaticMethods(): void
{
    assertFalse(DebugRuntime::runtimeEnabled(), 'Runtime should be disabled by default (static)');
    
    DebugRuntime::enableRuntime();
    assertTrue(DebugRuntime::runtimeEnabled(), 'Runtime should be enabled after calling enableRuntime()');
    
    DebugRuntime::disableRuntime();
    assertFalse(DebugRuntime::runtimeEnabled(), 'Runtime should be disabled after calling disableRuntime()');
    
    echo "✓ testDebugRuntimeStaticMethods passed\n";
}

// Run tests
try {
    testDebugRuntimeEnableDisable();
    testDebugRuntimeStaticMethods();
    echo "All tests passed!\n";
} catch (\Exception $e) {
    echo "Test failed: " . $e->getMessage() . "\n";
    exit(1);
}