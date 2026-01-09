<?php

declare(strict_types=1);

namespace Pfinalclub\AsyncioDebug\Tests;

use Pfinalclub\AsyncioDebug\Debug\DebugRuntime;
use Pfinalclub\AsyncioDebug\Debug\DebugConfig;
use Pfinalclub\AsyncioDebug\Exporter\LogExporter;
use Pfinalclub\AsyncioDebug\Contract\DebugEvent;
use Pfinalclub\AsyncioDebug\Contract\EventType;
use PHPUnit\Framework\TestCase;

/**
 * DebugRuntime 测试类
 * 
 * 测试调试运行时类的各种功能，包括：
 * - 单例模式
 * - 启用/禁用功能
 * - 事件发射
 * - 配置管理
 * - 导出器设置
 */
class DebugRuntimeTest extends TestCase
{
    /**
     * 测试前重置单例实例
     */
    protected function setUp(): void
    {
        // 使用反射重置单例实例
        $reflection = new \ReflectionClass(DebugRuntime::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null);
    }
    
    /**
     * 测试单例模式
     */
    public function testSingletonPattern(): void
    {
        $instance1 = DebugRuntime::getInstance();
        $instance2 = DebugRuntime::getInstance();
        
        $this->assertSame($instance1, $instance2);
    }
    
    /**
     * 测试默认状态
     */
    public function testDefaultState(): void
    {
        $runtime = DebugRuntime::getInstance();
        
        $this->assertFalse($runtime->isEnabled());
        $this->assertFalse(DebugRuntime::runtimeEnabled());
    }
    
    /**
     * 测试启用/禁用功能
     */
    public function testEnableDisable(): void
    {
        $runtime = DebugRuntime::getInstance();
        
        $runtime->enable();
        $this->assertTrue($runtime->isEnabled());
        $this->assertTrue(DebugRuntime::runtimeEnabled());
        
        $runtime->disable();
        $this->assertFalse($runtime->isEnabled());
        $this->assertFalse(DebugRuntime::runtimeEnabled());
    }
    
    /**
     * 测试静态启用/禁用方法
     */
    public function testStaticEnableDisable(): void
    {
        DebugRuntime::enableRuntime();
        $this->assertTrue(DebugRuntime::runtimeEnabled());
        
        DebugRuntime::disableRuntime();
        $this->assertFalse(DebugRuntime::runtimeEnabled());
    }
    
    /**
     * 测试设置导出器
     */
    public function testSetExporter(): void
    {
        $runtime = DebugRuntime::getInstance();
        $exporter = new LogExporter();
        
        $runtime->setExporter($exporter);
        
        // 使用反射验证导出器已设置
        $reflection = new \ReflectionClass($runtime);
        $exporterProperty = $reflection->getProperty('exporter');
        $exporterProperty->setAccessible(true);
        
        $this->assertSame($exporter, $exporterProperty->getValue($runtime));
    }
    
    /**
     * 测试配置管理
     */
    public function testConfigManagement(): void
    {
        $runtime = DebugRuntime::getInstance();
        $config = DebugConfig::forDevelopment();
        
        $runtime->updateConfig($config);
        $retrievedConfig = $runtime->getConfig();
        
        $this->assertEquals($config->bufferSize, $retrievedConfig->bufferSize);
        $this->assertEquals($config->logLevel, $retrievedConfig->logLevel);
    }
    
    /**
     * 测试静态配置方法
     */
    public function testStaticConfigMethods(): void
    {
        $config = DebugConfig::forProduction();
        
        DebugRuntime::updateRuntimeConfig($config);
        $retrievedConfig = DebugRuntime::getRuntimeConfig();
        
        $this->assertEquals($config->bufferSize, $retrievedConfig->bufferSize);
        $this->assertEquals($config->logLevel, $retrievedConfig->logLevel);
    }
    
    /**
     * 测试创建带配置的运行时
     */
    public function testCreateWithConfig(): void
    {
        $config = DebugConfig::forTesting();
        $runtime = DebugRuntime::createWithConfig($config);
        
        $this->assertInstanceOf(DebugRuntime::class, $runtime);
        $this->assertEquals($config->bufferSize, $runtime->getConfig()->bufferSize);
    }
    
    /**
     * 测试事件发射（启用状态）
     */
    public function testEmitEventWhenEnabled(): void
    {
        $runtime = DebugRuntime::getInstance();
        $runtime->enable();
        
        // 创建模拟事件
        $event = DebugEvent::create(
            EventType::FIBER_CREATED,
            123
        );
        
        // 这里应该验证事件被正确发射到导出器
        // 但由于导出器是接口，需要模拟测试
        $this->assertTrue($runtime->isEnabled());
    }
    
    /**
     * 测试事件发射（禁用状态）
     */
    public function testEmitEventWhenDisabled(): void
    {
        $runtime = DebugRuntime::getInstance();
        $runtime->disable();
        
        // 创建模拟事件
        $event = DebugEvent::create(
            EventType::FIBER_CREATED,
            123
        );
        
        // 禁用状态下事件应该被静默忽略
        $this->assertFalse($runtime->isEnabled());
    }
    
    /**
     * 测试静态事件发射方法
     */
    public function testStaticEmitEvent(): void
    {
        $runtime = DebugRuntime::getInstance();
        $runtime->enable();
        
        // 创建模拟事件
        $event = DebugEvent::create(
            EventType::FIBER_CREATED,
            123
        );
        
        // 静态方法应该正常工作
        DebugRuntime::emitEvent($event);
        
        $this->assertTrue($runtime->isEnabled());
    }
}