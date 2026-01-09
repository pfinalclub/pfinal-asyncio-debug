<?php

declare(strict_types=1);

namespace Pfinalclub\AsyncioDebug\Support;

/**
 * 唯一 ID 生成器
 * 
 * 为 Fiber 和 Task 生成唯一的标识符，确保事件的关联性。
 * 使用简单的递增计数器，保证 ID 的唯一性和单调性。
 * 
 * 设计原则：
 * - 简单高效，无性能开销
 * - 线程安全（单线程环境下）
 * - ID 类型分离，避免混淆
 * - 支持重置，便于测试
 * 
 * 使用示例：
 * ```php
 * $fiberId = IdGenerator::generateFiberId(); // 1
 * $taskId = IdGenerator::generateTaskId();   // 1
 * $fiberId2 = IdGenerator::generateFiberId(); // 2
 * ```
 */
final class IdGenerator
{
    /**
     * Fiber ID 计数器
     * 
     * 从 1 开始递增，每个调用都生成新的唯一 ID
     * 
     * @var int 当前 Fiber ID 计数值
     */
    private static int $fiberId = 0;

    /**
     * Task ID 计数器
     * 
     * 从 1 开始递增，每个调用都生成新的唯一 ID
     * 
     * @var int 当前 Task ID 计数值
     */
    private static int $taskId = 0;

    /**
     * 生成 Fiber 唯一 ID
     * 
     * 每次调用都会递增计数器并返回新的 ID。
     * 确保在同一进程内，每个 Fiber 都有唯一的标识符。
     * 
     * @return int 新生成的 Fiber ID（从 1 开始）
     */
    public static function generateFiberId(): int
    {
        return ++self::$fiberId;
    }

    /**
     * 生成 Task 唯一 ID
     * 
     * 每次调用都会递增计数器并返回新的 ID。
     * 确保在同一进程内，每个 Task 都有唯一的标识符。
     * 
     * @return int 新生成的 Task ID（从 1 开始）
     */
    public static function generateTaskId(): int
    {
        return ++self::$taskId;
    }

    /**
     * 重置所有计数器
     * 
     * 将 Fiber 和 Task ID 计数器重置为 0。
     * 主要用于测试环境，确保测试的独立性和可重复性。
     * 
     * 生产环境中通常不需要调用此方法。
     * 
     * @return void
     */
    public static function reset(): void
    {
        self::$fiberId = 0;
        self::$taskId = 0;
    }
}