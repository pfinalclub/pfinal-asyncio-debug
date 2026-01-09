<?php

declare(strict_types=1);

namespace Pfinalclub\AsyncioDebug\Support;

use Pfinalclub\AsyncioDebug\Contract\DebugEvent;

/**
 * 环形缓冲区实现
 * 
 * 用于存储调试事件，防止内存无限增长。
 * 当缓冲区满时，会自动覆盖最旧的事件，实现固定大小的存储。
 * 
 * 设计特点：
 * - 固定大小，防止 OOM
 * - FIFO（先进先出）行为
 * - 高效的内存使用
 * - 线程不安全（单线程环境）
 * 
 * 使用场景：
 * - EventStream: 存储最近的调试事件
 * - 生产环境: 限制内存使用
 * - 测试环境: 验证事件顺序
 * 
 * 工作原理：
 * ```
 * 空状态: head=0, tail=0, count=0
 * push: [0][1][2]...[size-1]
 *       ^head          ^tail
 * ```
 */
final class RingBuffer
{
    /**
     * 存储数组
     * 
     * 实际存储 DebugEvent 对象的数组
     * 
     * @var array<DebugEvent> 事件存储数组
     */
    private array $buffer = [];

    /**
     * 头指针（写入位置）
     * 
     * 指向下一个可以写入的位置
     * 写入后自动递增并循环
     * 
     * @var int 头指针索引
     */
    private int $head = 0;

    /**
     * 尾指针（读取位置）
     * 
     * 指向最旧的数据位置
     * 读取后自动递增并循环
     * 
     * @var int 尾指针索引
     */
    private int $tail = 0;

    /**
     * 当前元素数量
     * 
     * 缓冲区中当前存储的事件数量
     * 最大值为 size
     * 
     * @var int 当前元素数量
     */
    private int $count = 0;

    /**
     * 构造函数
     * 
     * @param int $size 缓冲区大小，必须为正数
     * @throws \InvalidArgumentException 当 size <= 0 时抛出
     */
    public function __construct(private readonly int $size)
    {
        if ($size <= 0) {
            throw new \InvalidArgumentException('Buffer size must be positive');
        }
    }

    /**
     * 推入事件
     * 
     * 将新事件添加到缓冲区。
     * 如果缓冲区已满，会覆盖最旧的事件。
     * 
     * @param DebugEvent $event 要添加的调试事件
     * @return void
     */
    public function push(DebugEvent $event): void
    {
        // 在头部位置写入事件
        $this->buffer[$this->head] = $event;
        
        // 移动头指针到下一个位置（循环）
        $this->head = ($this->head + 1) % $this->size;

        // 如果缓冲区未满，增加计数
        if ($this->count < $this->size) {
            $this->count++;
        } else {
            // 如果已满，移动尾指针（丢弃最旧数据）
            $this->tail = ($this->tail + 1) % $this->size;
        }
    }

    /**
     * 弹出事件
     * 
     * 移除并返回最旧的事件。
     * 如果缓冲区为空，返回 null。
     * 
     * @return DebugEvent|null 最旧的事件，如果为空则返回 null
     */
    public function pop(): ?DebugEvent
    {
        if ($this->count === 0) {
            return null;
        }

        // 获取尾部事件
        $event = $this->buffer[$this->tail];
        
        // 清除引用，帮助 GC
        unset($this->buffer[$this->tail]);
        
        // 移动尾指针
        $this->tail = ($this->tail + 1) % $this->size;
        
        // 减少计数
        $this->count--;

        return $event;
    }

    /**
     * 清空缓冲区
     * 
     * 移除并返回所有事件，按时间顺序排列（从旧到新）。
     * 操作后缓冲区变为空。
     * 
     * @return array<DebugEvent> 所有事件的数组
     */
    public function flush(): array
    {
        $events = [];

        // 逐一弹出所有事件
        while ($this->count > 0) {
            $events[] = $this->pop();
        }

        return $events;
    }

    /**
     * 查看所有事件
     * 
     * 返回所有事件但不移除它们。
     * 按时间顺序排列（从旧到新）。
     * 
     * @return array<DebugEvent> 所有事件的数组
     */
    public function peekAll(): array
    {
        $events = [];
        $index = $this->tail;

        // 从尾部开始，按顺序访问所有元素
        for ($i = 0; $i < $this->count; $i++) {
            $events[] = $this->buffer[$index];
            $index = ($index + 1) % $this->size;
        }

        return $events;
    }

    /**
     * 获取当前元素数量
     * 
     * @return int 当前存储的事件数量
     */
    public function count(): int
    {
        return $this->count;
    }

    /**
     * 检查缓冲区是否已满
     * 
     * @return bool 如果已满返回 true，否则返回 false
     */
    public function isFull(): bool
    {
        return $this->count >= $this->size;
    }

    /**
     * 检查缓冲区是否为空
     * 
     * @return bool 如果为空返回 true，否则返回 false
     */
    public function isEmpty(): bool
    {
        return $this->count === 0;
    }

    /**
     * 清空缓冲区
     * 
     * 立即移除所有事件并重置状态。
     * 与 flush() 不同，此方法不会返回被移除的事件。
     * 
     * @return void
     */
    public function clear(): void
    {
        $this->buffer = [];
        $this->head = 0;
        $this->tail = 0;
        $this->count = 0;
    }
}