<?php

declare(strict_types=1);

namespace Pfinalclub\AsyncioDebug\Tests;

use Pfinalclub\AsyncioDebug\Debug\DebugConfig;
use PHPUnit\Framework\TestCase;

/**
 * DebugConfig 测试类
 * 
 * 测试调试配置类的各种功能，包括：
 * - 默认配置创建
 * - 环境特定配置
 * - 参数验证
 * - 配置转换
 */
class DebugConfigTest extends TestCase
{
    /**
     * 测试默认配置
     */
    public function testDefaultConfig(): void
    {
        $config = DebugConfig::default();
        
        $this->assertEquals(1000, $config->bufferSize);
        $this->assertEquals('info', $config->logLevel);
        $this->assertFalse($config->enableSampling);
        $this->assertEquals(0.1, $config->samplingRate);
        $this->assertFalse($config->enablePerformanceMonitoring);
        $this->assertFalse($config->enableVerboseErrorReporting);
    }
    
    /**
     * 测试开发环境配置
     */
    public function testDevelopmentConfig(): void
    {
        $config = DebugConfig::forDevelopment();
        
        $this->assertEquals(5000, $config->bufferSize);
        $this->assertEquals('debug', $config->logLevel);
        $this->assertFalse($config->enableSampling);
        $this->assertTrue($config->enablePerformanceMonitoring);
        $this->assertTrue($config->enableVerboseErrorReporting);
        $this->assertTrue($config->isDevelopment());
    }
    
    /**
     * 测试生产环境配置
     */
    public function testProductionConfig(): void
    {
        $config = DebugConfig::forProduction();
        
        $this->assertEquals(500, $config->bufferSize);
        $this->assertEquals('warning', $config->logLevel);
        $this->assertTrue($config->enableSampling);
        $this->assertEquals(0.1, $config->samplingRate);
        $this->assertFalse($config->enablePerformanceMonitoring);
        $this->assertFalse($config->enableVerboseErrorReporting);
        $this->assertTrue($config->isProduction());
    }
    
    /**
     * 测试测试环境配置
     */
    public function testTestingConfig(): void
    {
        $config = DebugConfig::forTesting();
        
        $this->assertEquals(1000, $config->bufferSize);
        $this->assertEquals('info', $config->logLevel);
        $this->assertFalse($config->enableSampling);
        $this->assertTrue($config->enablePerformanceMonitoring);
        $this->assertTrue($config->enableVerboseErrorReporting);
    }
    
    /**
     * 测试参数验证 - 缓冲区大小
     */
    public function testInvalidBufferSize(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('缓冲区大小必须为正数');
        
        new DebugConfig(bufferSize: 0);
    }
    
    /**
     * 测试参数验证 - 无效日志级别
     */
    public function testInvalidLogLevel(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('无效的日志级别');
        
        new DebugConfig(logLevel: 'invalid');
    }
    
    /**
     * 测试参数验证 - 无效采样率
     */
    public function testInvalidSamplingRate(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('采样率必须在 0.0 到 1.0 之间');
        
        new DebugConfig(enableSampling: true, samplingRate: 1.5);
    }
    
    /**
     * 测试配置转换为数组
     */
    public function testToArray(): void
    {
        $config = DebugConfig::default();
        $array = $config->toArray();
        
        $this->assertIsArray($array);
        $this->assertArrayHasKey('buffer_size', $array);
        $this->assertArrayHasKey('log_level', $array);
        $this->assertArrayHasKey('enable_sampling', $array);
        $this->assertArrayHasKey('sampling_rate', $array);
        $this->assertArrayHasKey('enable_performance_monitoring', $array);
        $this->assertArrayHasKey('enable_verbose_error_reporting', $array);
        
        $this->assertEquals(1000, $array['buffer_size']);
        $this->assertEquals('info', $array['log_level']);
    }
    
    /**
     * 测试自定义配置
     */
    public function testCustomConfig(): void
    {
        $config = new DebugConfig(
            bufferSize: 2000,
            logLevel: 'error',
            enableSampling: true,
            samplingRate: 0.5,
            enablePerformanceMonitoring: true,
            enableVerboseErrorReporting: true
        );
        
        $this->assertEquals(2000, $config->bufferSize);
        $this->assertEquals('error', $config->logLevel);
        $this->assertTrue($config->enableSampling);
        $this->assertEquals(0.5, $config->samplingRate);
        $this->assertTrue($config->enablePerformanceMonitoring);
        $this->assertTrue($config->enableVerboseErrorReporting);
    }
}