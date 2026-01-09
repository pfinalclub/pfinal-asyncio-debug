<?php

declare(strict_types=1);

namespace Pfinalclub\AsyncioDebug\Tests;

use Pfinalclub\AsyncioDebug\Exception\DebugException;
use Pfinalclub\AsyncioDebug\Exception\InvalidEventException;
use Pfinalclub\AsyncioDebug\Exception\ExporterException;
use PHPUnit\Framework\TestCase;

/**
 * 异常类测试
 * 
 * 测试自定义异常类的各种功能，包括：
 * - 异常创建
 * - 错误码和消息
 * - 上下文信息
 * - 异常继承关系
 */
class ExceptionTest extends TestCase
{
    /**
     * 测试基础异常类
     */
    public function testDebugException(): void
    {
        $exception = new DebugException('测试错误消息', 123);
        
        $this->assertEquals('测试错误消息', $exception->getMessage());
        $this->assertEquals(123, $exception->getCode());
        $this->assertEmpty($exception->getContext());
    }
    
    /**
     * 测试带上下文的异常
     */
    public function testDebugExceptionWithContext(): void
    {
        $context = ['key' => 'value', 'number' => 42];
        $exception = new DebugException('测试错误消息', 123, null, $context);
        
        $this->assertEquals($context, $exception->getContext());
        $this->assertEquals('value', $exception->getContext()['key']);
        $this->assertEquals(42, $exception->getContext()['number']);
    }
    
    /**
     * 测试配置错误异常工厂方法
     */
    public function testConfigErrorFactory(): void
    {
        $context = ['config_key' => 'buffer_size'];
        $exception = DebugException::configError('配置错误', $context);
        
        $this->assertEquals('配置错误', $exception->getMessage());
        $this->assertEquals(DebugException::CODE_CONFIG_ERROR, $exception->getCode());
        $this->assertEquals($context, $exception->getContext());
    }
    
    /**
     * 测试运行时错误异常工厂方法
     */
    public function testRuntimeErrorFactory(): void
    {
        $exception = DebugException::runtimeError('运行时错误');
        
        $this->assertEquals('运行时错误', $exception->getMessage());
        $this->assertEquals(DebugException::CODE_RUNTIME_ERROR, $exception->getCode());
    }
    
    /**
     * 测试导出器错误异常工厂方法
     */
    public function testExporterErrorFactory(): void
    {
        $exception = DebugException::exporterError('导出器错误');
        
        $this->assertEquals('导出器错误', $exception->getMessage());
        $this->assertEquals(DebugException::CODE_EXPORTER_ERROR, $exception->getCode());
    }
    
    /**
     * 测试事件错误异常工厂方法
     */
    public function testEventErrorFactory(): void
    {
        $exception = DebugException::eventError('事件错误');
        
        $this->assertEquals('事件错误', $exception->getMessage());
        $this->assertEquals(DebugException::CODE_EVENT_ERROR, $exception->getCode());
    }
    
    /**
     * 测试无效事件异常
     */
    public function testInvalidEventException(): void
    {
        $exception = InvalidEventException::invalidEventType('invalid_type');
        
        $this->assertStringContainsString('无效的事件类型: invalid_type', $exception->getMessage());
        $this->assertEquals(InvalidEventException::CODE_INVALID_EVENT_TYPE, $exception->getCode());
    }
    
    /**
     * 测试无效载荷异常
     */
    public function testInvalidPayloadException(): void
    {
        $exception = InvalidEventException::invalidPayload('timestamp');
        
        $this->assertStringContainsString('事件载荷字段无效: timestamp', $exception->getMessage());
        $this->assertEquals(InvalidEventException::CODE_INVALID_PAYLOAD, $exception->getCode());
    }
    
    /**
     * 测试无效时间戳异常
     */
    public function testInvalidTimestampException(): void
    {
        $exception = InvalidEventException::invalidTimestamp(-1);
        
        $this->assertStringContainsString('无效的事件时间戳: -1', $exception->getMessage());
        $this->assertEquals(InvalidEventException::CODE_INVALID_TIMESTAMP, $exception->getCode());
    }
    
    /**
     * 测试无效 Fiber ID 异常
     */
    public function testInvalidFiberIdException(): void
    {
        $exception = InvalidEventException::invalidFiberId(-5);
        
        $this->assertStringContainsString('无效的 Fiber ID: -5', $exception->getMessage());
        $this->assertEquals(InvalidEventException::CODE_INVALID_FIBER_ID, $exception->getCode());
    }
    
    /**
     * 测试导出器异常
     */
    public function testExporterException(): void
    {
        $exception = ExporterException::logExportError('日志写入失败');
        
        $this->assertEquals('日志写入失败', $exception->getMessage());
        $this->assertEquals(ExporterException::CODE_LOG_EXPORT_ERROR, $exception->getCode());
    }
    
    /**
     * 测试文件导出异常
     */
    public function testFileExportException(): void
    {
        $exception = ExporterException::fileExportError('/path/to/file.log', '权限不足');
        
        $this->assertStringContainsString('文件导出失败: /path/to/file.log (权限不足)', $exception->getMessage());
        $this->assertEquals(ExporterException::CODE_FILE_EXPORT_ERROR, $exception->getCode());
    }
    
    /**
     * 测试网络导出异常
     */
    public function testNetworkExportException(): void
    {
        $exception = ExporterException::networkExportError('http://example.com/api', '连接超时');
        
        $this->assertStringContainsString('网络导出失败: http://example.com/api (连接超时)', $exception->getMessage());
        $this->assertEquals(ExporterException::CODE_NETWORK_EXPORT_ERROR, $exception->getCode());
    }
    
    /**
     * 测试异常继承关系
     */
    public function testExceptionInheritance(): void
    {
        $debugException = new DebugException('测试');
        $invalidEventException = new InvalidEventException('测试');
        $exporterException = new ExporterException('测试');
        
        $this->assertInstanceOf(\RuntimeException::class, $debugException);
        $this->assertInstanceOf(DebugException::class, $invalidEventException);
        $this->assertInstanceOf(DebugException::class, $exporterException);
    }
}