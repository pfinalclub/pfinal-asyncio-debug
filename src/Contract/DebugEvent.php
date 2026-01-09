<?php

declare(strict_types=1);

namespace Pfinalclub\AsyncioDebug\Contract;

/**
 * 调试事件类
 * 
 * 这是 asyncio-debug 的核心数据结构，代表一个发生的异步事件。
 * 每个事件都包含时间戳、事件类型、关联的 Fiber/Task ID 和详细载荷。
 * 
 * 设计原则：
 * - 不可变对象，一旦创建就不可修改
 * - 只包含事实数据，不做解释和判断
 * - 结构化数据，便于序列化和分析
 * 
 * 使用示例：
 * ```php
 * $event = DebugEvent::create(
 *     EventType::FIBER_SUSPENDED,
 *     $fiberId,
 *     null,
 *     ['reason' => 'waiting for I/O']
 * );
 * ```
 */
final readonly class DebugEvent
{
    /**
     * 事件类型
     * 
     * @var EventType 事件的枚举类型
     */
    public EventType $type;

    /**
     * 事件时间戳
     * 
     * 使用 microtime(true) 获取的高精度时间戳，
     * 单位为秒，包含微秒精度
     * 
     * @var float 事件发生的时间戳
     */
    public float $timestamp;

    /**
     * 关联的 Fiber ID
     * 
     * 表示事件发生在哪个 Fiber 中。
     * 对于系统级别的事件（如 LOOP_TICK），可能为 0。
     * 
     * @var int Fiber 的唯一标识符
     */
    public int $fiberId;

    /**
     * 关联的 Task ID
     * 
     * 表示事件发生在哪个 Task 中。
     * 对于 Fiber 级别的事件，可能为 null。
     * 
     * @var int|null Task 的唯一标识符，如果无关则为 null
     */
    public ?int $taskId;

    /**
     * 事件载荷
     * 
     * 包含事件的详细信息，具体内容取决于事件类型。
     * 例如 FIBER_SUSPENDED 事件可能包含暂停原因。
     * 
     * @var EventPayload 事件的详细数据
     */
    public EventPayload $payload;

    /**
     * 构造函数
     * 
     * @param EventType $type 事件类型
     * @param float $timestamp 时间戳
     * @param int $fiberId Fiber ID
     * @param int|null $taskId Task ID
     * @param EventPayload $payload 事件载荷
     */
    public function __construct(
        EventType $type,
        float $timestamp,
        int $fiberId,
        ?int $taskId,
        EventPayload $payload
    ) {
        $this->type = $type;
        $this->timestamp = $timestamp;
        $this->fiberId = $fiberId;
        $this->taskId = $taskId;
        $this->payload = $payload;
    }

    /**
     * 创建调试事件实例的工厂方法
     * 
     * 这是一个便捷方法，自动生成时间戳和载荷对象。
     * 推荐使用此方法创建事件实例。
     * 
     * @param EventType $type 事件类型
     * @param int $fiberId Fiber ID
     * @param int|null $taskId Task ID（可选）
     * @param array $payloadData 载荷数据数组（可选）
     * @return self 新的调试事件实例
     */
    public static function create(
        EventType $type,
        int $fiberId,
        ?int $taskId = null,
        array $payloadData = []
    ): self {
        return new self(
            $type,
            microtime(true), // 自动生成高精度时间戳
            $fiberId,
            $taskId,
            EventPayload::create($payloadData) // 自动创建载荷对象
        );
    }

    /**
     * 转换为数组格式
     * 
     * 用于序列化、日志输出或数据分析。
     * 返回的数组结构清晰，便于处理。
     * 
     * @return array 事件数据的数组表示
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type->value,
            'timestamp' => $this->timestamp,
            'fiber_id' => $this->fiberId,
            'task_id' => $this->taskId,
            'payload' => $this->payload->all(),
        ];
    }
}