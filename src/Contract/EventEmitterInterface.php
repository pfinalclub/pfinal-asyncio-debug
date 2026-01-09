<?php

declare(strict_types=1);

namespace Pfinalclub\AsyncioDebug\Contract;

/**
 * 事件发射器接口
 * 
 * 定义了事件发射器必须实现的基本功能。
 * 这是 asyncio-debug 的核心接口，确保所有发射器行为一致。
 * 
 * 设计原则：
 * - 简单明了，只包含必要的方法
 * - 状态管理清晰，支持启用/禁用控制
 * - 类型安全，明确的方法签名
 * 
 * 实现类：
 * - DebugRuntime: 主要的事件发射器实现
 */
interface EventEmitterInterface
{
    /**
     * 发射事件
     * 
     * 将调试事件发送到导出器或处理器。
     * 如果发射器被禁用，应该静默忽略事件。
     * 
     * @param DebugEvent $event 要发射的调试事件
     * @return void
     */
    public function emit(DebugEvent $event): void;
    
    /**
     * 检查发射器是否启用
     * 
     * @return bool 如果启用返回 true，否则返回 false
     */
    public function isEnabled(): bool;
    
    /**
     * 启用事件发射
     * 
     * 启用后，emit() 方法会将事件发送到导出器。
     * 
     * @return void
     */
    public function enable(): void;
    
    /**
     * 禁用事件发射
     * 
     * 禁用后，emit() 方法将静默忽略所有事件。
     * 这是实现"零侵入"和"可丢弃"原则的关键。
     * 
     * @return void
     */
    public function disable(): void;
}