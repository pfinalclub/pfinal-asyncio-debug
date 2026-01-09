<?php

declare(strict_types=1);

namespace Pfinalclub\AsyncioDebug\Debug;

use Pfinalclub\AsyncioDebug\Contract\DebugEvent;
use Pfinalclub\AsyncioDebug\Contract\EventType;
use Pfinalclub\AsyncioDebug\Support\IdGenerator;

/**
 * Fiber 生命周期跟踪器
 * 
 * 专门负责跟踪和记录 Fiber 的完整生命周期。
 * 这是理解异步行为的核心组件，记录每个 Fiber 的状态变化。
 * 
 * 设计原则：
 * - 只记录事实，不做解释
 * - 不持有 Fiber 实例，避免内存泄漏
 * - 自动生成唯一 ID
 * - 支持跟踪活跃 Fiber 数量
 * 
 * 跟踪的事件：
 * 1. FIBER_CREATED - Fiber 创建
 * 2. FIBER_STARTED - Fiber 开始执行
 * 3. FIBER_SUSPENDED - Fiber 暂停（等待 I/O 等）
 * 4. FIBER_RESUMED - Fiber 恢复执行
 * 5. FIBER_TERMINATED - Fiber 终止
 * 
 * 使用场景：
 * - 分析异步执行模式
 * - 检测 Fiber 泄漏
 * - 理解并发行为
 * - 性能调优参考
 */
final class FiberTracker
{
    /**
     * 跟踪的 Fiber 集合
     * 
     * 存储当前活跃的 Fiber 信息。
     * 注意：这里只存储 Fiber ID，不存储 Fiber 实例，
     * 避免阻止垃圾回收。
     * 
     * @var array<int, mixed> 活跃 Fiber 的映射
     */
    private array $fibers = [];

    /**
     * 处理 Fiber 创建事件
     * 
     * 当新的 Fiber 被创建时调用此方法。
     * 自动生成唯一 ID 并记录创建事件。
     * 
     * @return int 新生成的 Fiber ID
     */
    public function onFiberCreated(): int
    {
        // 生成唯一的 Fiber ID
        $fiberId = IdGenerator::generateFiberId();
        
        // 记录 Fiber 创建事件
        $event = DebugEvent::create(
            EventType::FIBER_CREATED,
            $fiberId,
            null, // Fiber 创建时没有关联的 Task
            ['created_at' => microtime(true)]
        );

        // 发射事件到调试运行时
        DebugRuntime::emitEvent($event);

        return $fiberId;
    }

    /**
     * 处理 Fiber 启动事件
     * 
     * 当 Fiber 开始执行时调用此方法。
     * 标志着 Fiber 从创建状态转为活跃执行状态。
     * 
     * @param int $fiberId Fiber 的唯一标识符
     * @return void
     */
    public function onFiberStarted(int $fiberId): void
    {
        $event = DebugEvent::create(
            EventType::FIBER_STARTED,
            $fiberId,
            null,
            ['started_at' => microtime(true)]
        );

        DebugRuntime::emitEvent($event);
    }

    /**
     * 处理 Fiber 暂停事件
     * 
     * 当 Fiber 因等待 I/O、锁等原因暂停执行时调用。
     * 这是理解异步行为的关键事件，暂停原因很重要。
     * 
     * 常见暂停原因：
     * - 'await Http::get()' - 等待网络请求
     * - 'await Semaphore' - 等待信号量
     * - 'await Sleep' - 等待定时器
     * 
     * @param int $fiberId Fiber 的唯一标识符
     * @param string $reason 暂停的原因描述
     * @return void
     */
    public function onFiberSuspended(int $fiberId, string $reason = ''): void
    {
        $event = DebugEvent::create(
            EventType::FIBER_SUSPENDED,
            $fiberId,
            null,
            [
                'reason' => $reason,
                'suspended_at' => microtime(true)
            ]
        );

        DebugRuntime::emitEvent($event);
    }

    /**
     * 处理 Fiber 恢复事件
     * 
     * 当 Fiber 从暂停状态恢复执行时调用。
     * 通常意味着等待条件已满足（如 I/O 完成）。
     * 
     * @param int $fiberId Fiber 的唯一标识符
     * @return void
     */
    public function onFiberResumed(int $fiberId): void
    {
        $event = DebugEvent::create(
            EventType::FIBER_RESUMED,
            $fiberId,
            null,
            ['resumed_at' => microtime(true)]
        );

        DebugRuntime::emitEvent($event);
    }

    /**
     * 处理 Fiber 终止事件
     * 
     * 当 Fiber 执行完成（正常或异常）时调用。
     * 清理内部跟踪状态，避免内存泄漏。
     * 
     * @param int $fiberId Fiber 的唯一标识符
     * @return void
     */
    public function onFiberTerminated(int $fiberId): void
    {
        $event = DebugEvent::create(
            EventType::FIBER_TERMINATED,
            $fiberId,
            null,
            ['terminated_at' => microtime(true)]
        );

        DebugRuntime::emitEvent($event);

        // 从跟踪集合中移除，避免内存泄漏
        unset($this->fibers[$fiberId]);
    }

    /**
     * 获取当前跟踪的 Fiber 数量
     * 
     * 用于监控 Fiber 泄漏或活跃 Fiber 数量。
     * 如果这个数量持续增长，可能存在 Fiber 泄漏。
     * 
     * @return int 当前活跃 Fiber 的数量
     */
    public function getTrackedFiberCount(): int
    {
        return count($this->fibers);
    }
}