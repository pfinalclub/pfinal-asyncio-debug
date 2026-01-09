<?php

declare(strict_types=1);

namespace Pfinalclub\AsyncioDebug\Exception;

/**
 * asyncio-debug 基础异常类
 * 
 * 所有 asyncio-debug 相关异常的基类。
 * 提供统一的异常处理机制和错误信息格式。
 * 
 * 设计原则：
 * - 继承自 RuntimeException，表示运行时错误
 * - 支持自定义错误码和上下文信息
 * - 提供友好的错误消息格式
 * 
 * 使用场景：
 * - 配置错误
 * - 运行时状态错误
 * - 导出器错误
 * - 事件处理错误
 */
class DebugException extends \RuntimeException
{
    /**
     * 错误码：配置错误
     */
    public const CODE_CONFIG_ERROR = 1000;
    
    /**
     * 错误码：运行时错误
     */
    public const CODE_RUNTIME_ERROR = 2000;
    
    /**
     * 错误码：导出器错误
     */
    public const CODE_EXPORTER_ERROR = 3000;
    
    /**
     * 错误码：事件处理错误
     */
    public const CODE_EVENT_ERROR = 4000;
    
    /**
     * 异常上下文信息
     * 
     * @var array 包含异常相关上下文数据的数组
     */
    private array $context = [];
    
    /**
     * 构造函数
     * 
     * @param string $message 错误消息
     * @param int $code 错误码
     * @param \Throwable|null $previous 前一个异常
     * @param array $context 异常上下文信息
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }
    
    /**
     * 获取异常上下文信息
     * 
     * @return array 异常上下文信息
     */
    public function getContext(): array
    {
        return $this->context;
    }
    
    /**
     * 创建配置错误异常
     * 
     * @param string $message 错误消息
     * @param array $context 上下文信息
     * @return self
     */
    public static function configError(string $message, array $context = []): self
    {
        return new self($message, self::CODE_CONFIG_ERROR, null, $context);
    }
    
    /**
     * 创建运行时错误异常
     * 
     * @param string $message 错误消息
     * @param array $context 上下文信息
     * @return self
     */
    public static function runtimeError(string $message, array $context = []): self
    {
        return new self($message, self::CODE_RUNTIME_ERROR, null, $context);
    }
    
    /**
     * 创建导出器错误异常
     * 
     * @param string $message 错误消息
     * @param array $context 上下文信息
     * @return self
     */
    public static function exporterError(string $message, array $context = []): self
    {
        return new self($message, self::CODE_EXPORTER_ERROR, null, $context);
    }
    
    /**
     * 创建事件处理错误异常
     * 
     * @param string $message 错误消息
     * @param array $context 上下文信息
     * @return self
     */
    public static function eventError(string $message, array $context = []): self
    {
        return new self($message, self::CODE_EVENT_ERROR, null, $context);
    }
}