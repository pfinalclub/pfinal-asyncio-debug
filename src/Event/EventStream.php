<?php

declare(strict_types=1);

namespace Pfinalclub\AsyncioDebug\Event;

use Pfinalclub\AsyncioDebug\Contract\DebugEvent;
use Pfinalclub\AsyncioDebug\Support\RingBuffer;

/**
 * 事件流管理器
 * 
 * 基于环形缓冲区的事件流实现，提供事件的存储和管理功能。
 * 这是 asyncio-debug 事件处理的核心组件，连接事件生产者和消费者。
 * 
 * 设计特点：
 * - 基于环形缓冲区，防止内存无限增长
 * - 保证事件的时间顺序
 * - 支持批量操作和实时查询
 * - 线程不安全（单线程环境）
 * 
 * 使用场景：
 * - DebugRuntime: 临时存储待导出的事件
 * - Exporter: 批量获取事件进行导出
 * - Testing: 验证事件的正确性和顺序
 * 
 * 缓冲区大小选择：
 * - 开发环境: 1000-5000，保留足够历史用于调试
 * - 生产环境: 100-500，控制内存使用
 * - 测试环境: 100-1000，足够验证逻辑
 * 
 * 使用示例：
 * ```php
 * $stream = new EventStream(1000);
 * $stream->push($event1);
 * $stream->push($event2);
 * 
 * // 批量获取并清空
 * $events = $stream->flush();
 * 
 * // 查看但不移除
 * $allEvents = $stream->peekAll();
 * ```
 */
final class EventStream
{
    /**
     * 环形缓冲区
     * 
     * 实际存储事件的缓冲区，自动处理空间管理。
     * 当缓冲区满时，会自动覆盖最旧的事件。
     * 
     * @var RingBuffer 底层存储的环形缓冲区
     */
    private RingBuffer $buffer;

    /**
     * 构造函数
     * 
     * 创建指定大小的事件流。
     * 缓冲区大小应根据实际使用场景选择：
     * - 调试模式: 较大缓冲区，保留更多历史
     * - 生产模式: 较小缓冲区，控制内存占用
     * 
     * @param int $bufferSize 缓冲区大小，默认 1000
     */
    public function __construct(int $bufferSize = 1000)
    {
        $this->buffer = new RingBuffer($bufferSize);
    }

    /**
     * 推入事件
     * 
     * 将新事件添加到事件流中。
     * 如果缓冲区已满，最旧的事件会被覆盖。
     * 
     * @param DebugEvent $event 要添加的调试事件
     * @return void
     */
    public function push(DebugEvent $event): void
    {
        $this->buffer->push($event);
    }

    /**
     * 刷新事件流
     * 
     * 移除并返回所有事件，按时间顺序排列（从旧到新）。
     * 操作后事件流变为空，这个方法通常用于批量导出。
     * 
     * @return iterable<DebugEvent> 所有事件的迭代器
     */
    public function flush(): iterable
    {
        return $this->buffer->flush();
    }

    /**
     * 查看所有事件
     * 
     * 返回所有事件但不移除它们，按时间顺序排列。
     * 这个方法用于查看当前状态而不影响事件流。
     * 
     * @return iterable<DebugEvent> 所有事件的迭代器
     */
    public function peekAll(): iterable
    {
        return $this->buffer->peekAll();
    }

    /**
     * 获取当前事件数量
     * 
     * @return int 当前存储的事件数量
     */
    public function count(): int
    {
        return $this->buffer->count();
    }

    /**
     * 清空事件流
     * 
     * 立即移除所有事件，不返回被移除的事件。
     * 用于强制清理或重置状态。
     * 
     * @return void
     */
    public function clear(): void
    {
        $this->buffer->clear();
    }

    /**
     * 检查事件流是否已满
     * 
     * @return bool 如果已满返回 true，否则返回 false
     */
    public function isFull(): bool
    {
        return $this->buffer->isFull();
    }

    /**
     * 检查事件流是否为空
     * 
     * @return bool 如果为空返回 true，否则返回 false
     */
    public function isEmpty(): bool
    {
        return $this->buffer->isEmpty();
    }
}