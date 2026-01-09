<?php

declare(strict_types=1);

namespace Pfinalclub\AsyncioDebug\Contract;

/**
 * 事件类型枚举
 * 
 * 这是 asyncio-debug 的核心协议，定义了所有可能发生的事件类型。
 * 一旦发布，就是兼容性承诺，不得随意修改。
 * 
 * 事件命名采用 "domain.action" 格式，确保语义清晰、易于理解。
 * 
 * Fiber 相关事件：
 * - fiber.*: Fiber 生命周期事件，记录 Fiber 的创建、启动、暂停、恢复、终止
 * 
 * Task 相关事件：  
 * - task.*: Task 生命周期事件，记录任务的提交、启动、完成、失败
 * 
 * Await 相关事件：
 * - await.*: 异步等待事件，记录 await 的进入和退出
 * 
 * 系统事件：
 * - loop.tick: 事件循环 tick 事件，记录每次循环迭代
 */
enum EventType: string
{
    /**
     * Fiber 创建事件
     * 
     * 当新的 Fiber 实例被创建时触发
     * 标志着异步执行单元的诞生
     */
    case FIBER_CREATED = 'fiber.created';

    /**
     * Fiber 启动事件
     * 
     * 当 Fiber 开始执行时触发
     * 标志着异步代码真正开始运行
     */
    case FIBER_STARTED = 'fiber.started';

    /**
     * Fiber 暂停事件
     * 
     * 当 Fiber 因等待 I/O、锁等原因暂停执行时触发
     * 这是理解异步行为的关键事件
     * 
     * payload 通常包含暂停原因
     */
    case FIBER_SUSPENDED = 'fiber.suspended';

    /**
     * Fiber 恢复事件
     * 
     * 当 Fiber 从暂停状态恢复执行时触发
     * 标志着等待条件已满足
     */
    case FIBER_RESUMED = 'fiber.resumed';

    /**
     * Fiber 终止事件
     * 
     * 当 Fiber 执行完成（正常或异常）时触发
     * 标志着异步执行单元的生命周期结束
     */
    case FIBER_TERMINATED = 'fiber.terminated';

    /**
     * Task 提交事件
     * 
     * 当异步任务被提交到调度器时触发
     * 标志着任务进入执行队列
     */
    case TASK_SUBMITTED = 'task.submitted';

    /**
     * Task 启动事件
     * 
     * 当任务开始被调度执行时触发
     * 标志着任务从等待状态进入活跃状态
     */
    case TASK_STARTED = 'task.started';

    /**
     * Task 完成事件
     * 
     * 当任务成功完成执行时触发
     * payload 通常包含结果类型信息
     */
    case TASK_COMPLETED = 'task.completed';

    /**
     * Task 失败事件
     * 
     * 当任务执行过程中抛出异常时触发
     * payload 通常包含错误类型和错误信息
     */
    case TASK_FAILED = 'task.failed';

    /**
     * Await 进入事件
     * 
     * 当代码开始 await 某个异步操作时触发
     * 这是理解异步调用链的关键事件
     * 
     * payload 通常包含等待的目标（如 Http::get()）
     */
    case AWAIT_ENTER = 'await.enter';

    /**
     * Await 退出事件
     * 
     * 当 await 的异步操作完成时触发
     * 标志着等待结束，代码继续执行
     * 
     * payload 通常包含等待的目标
     */
    case AWAIT_EXIT = 'await.exit';

    /**
     * 事件循环 Tick 事件
     * 
     * 每次事件循环迭代时触发
     * 用于监控事件循环的健康状态和性能
     * 
     * payload 通常包含 tick 时间戳
     */
    case LOOP_TICK = 'loop.tick';
}