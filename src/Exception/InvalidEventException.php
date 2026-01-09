<?php

declare(strict_types=1);

namespace Pfinalclub\AsyncioDebug\Exception;

/**
 * 无效事件异常
 * 
 * 当事件数据无效或不符合协议时抛出。
 * 例如：缺少必要字段、类型不匹配、数据格式错误等。
 * 
 * 使用场景：
 * - 事件类型不存在
 * - 事件载荷格式错误
 * - 事件时间戳无效
 * - Fiber ID 无效
 */
class InvalidEventException extends DebugException
{
    /**
     * 错误码：事件类型错误
     */
    public const CODE_INVALID_EVENT_TYPE = 4100;
    
    /**
     * 错误码：事件载荷错误
     */
    public const CODE_INVALID_PAYLOAD = 4101;
    
    /**
     * 错误码：事件时间戳错误
     */
    public const CODE_INVALID_TIMESTAMP = 4102;
    
    /**
     * 错误码：Fiber ID 错误
     */
    public const CODE_INVALID_FIBER_ID = 4103;
    
    /**
     * 创建事件类型错误异常
     * 
     * @param string $eventType 无效的事件类型
     * @param array $context 上下文信息
     * @return self
     */
    public static function invalidEventType(string $eventType, array $context = []): self
    {
        $message = sprintf('无效的事件类型: %s', $eventType);
        return new self($message, self::CODE_INVALID_EVENT_TYPE, null, $context);
    }
    
    /**
     * 创建事件载荷错误异常
     * 
     * @param string $field 无效的字段名
     * @param array $context 上下文信息
     * @return self
     */
    public static function invalidPayload(string $field, array $context = []): self
    {
        $message = sprintf('事件载荷字段无效: %s', $field);
        return new self($message, self::CODE_INVALID_PAYLOAD, null, $context);
    }
    
    /**
     * 创建事件时间戳错误异常
     * 
     * @param mixed $timestamp 无效的时间戳
     * @param array $context 上下文信息
     * @return self
     */
    public static function invalidTimestamp($timestamp, array $context = []): self
    {
        $message = sprintf('无效的事件时间戳: %s', var_export($timestamp, true));
        return new self($message, self::CODE_INVALID_TIMESTAMP, null, $context);
    }
    
    /**
     * 创建 Fiber ID 错误异常
     * 
     * @param mixed $fiberId 无效的 Fiber ID
     * @param array $context 上下文信息
     * @return self
     */
    public static function invalidFiberId($fiberId, array $context = []): self
    {
        $message = sprintf('无效的 Fiber ID: %s', var_export($fiberId, true));
        return new self($message, self::CODE_INVALID_FIBER_ID, null, $context);
    }
}