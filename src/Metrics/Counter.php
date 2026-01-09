<?php

declare(strict_types=1);

namespace Pfinalclub\AsyncioDebug\Metrics;

/**
 * 计数器指标
 * 
 * 提供简单的计数功能，用于统计各种调试指标。
 * 这是 asyncio-debug 的原始指标收集器，只提供基本的计数功能。
 * 
 * 设计原则：
 * - 简单高效，无复杂逻辑
 * - 原子操作（单线程环境）
 * - 支持增减和重置
 * - 命名清晰，便于识别
 * 
 * 使用场景：
 * - fiber.created: 统计创建的 Fiber 总数
 * - task.completed: 统计完成的任务总数
 * - await.enter: 统计 await 操作总次数
 * - loop.tick: 统计事件循环迭代次数
 * 
 * 不提供：
 * - 平均值计算
 * - 时间窗口统计
 * - 百分位数统计
 * - 复杂的聚合逻辑
 * 
 * 使用示例：
 * ```php
 * $fiberCreated = new Counter('fiber.created');
 * $fiberCreated->inc();  // 增加计数
 * $fiberCreated->inc(5); // 增加指定数量
 * $total = $fiberCreated->get(); // 获取当前值
 * $fiberCreated->reset(); // 重置为 0
 * ```
 */
final class Counter
{
    /**
     * 当前计数值
     * 
     * 存储计数器的当前值，初始为 0。
     * 支持正数、负数和零值。
     * 
     * @var int 当前计数值
     */
    private int $value = 0;

    /**
     * 计数器名称
     * 
     * 计数器的唯一标识符，用于区分不同的指标。
     * 建议使用点分命名法，如 "fiber.created"。
     * 
     * @var string 计数器的名称
     */
    public function __construct(private readonly string $name)
    {
        // 构造函数只需要设置名称，计数值初始化为 0
    }

    /**
     * 增加计数值
     * 
     * 将当前计数值增加指定的数量。
     * 默认增加 1，也可以指定任意正整数。
     * 
     * @param int $delta 要增加的数量，默认为 1
     * @return void
     */
    public function inc(int $delta = 1): void
    {
        $this->value += $delta;
    }

    /**
     * 减少计数值
     * 
     * 将当前计数值减少指定的数量。
     * 默认减少 1，也可以指定任意正整数。
     * 注意：计数值可能变为负数。
     * 
     * @param int $delta 要减少的数量，默认为 1
     * @return void
     */
    public function dec(int $delta = 1): void
    {
        $this->value -= $delta;
    }

    /**
     * 获取当前计数值
     * 
     * @return int 当前的计数值
     */
    public function get(): int
    {
        return $this->value;
    }

    /**
     * 重置计数器
     * 
     * 将计数值重置为 0。
     * 主要用于测试或重新开始统计。
     * 
     * @return void
     */
    public function reset(): void
    {
        $this->value = 0;
    }

    /**
     * 获取计数器名称
     * 
     * @return string 计数器的名称
     */
    public function getName(): string
    {
        return $this->name;
    }
}