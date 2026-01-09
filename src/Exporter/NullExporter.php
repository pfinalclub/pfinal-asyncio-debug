<?php

declare(strict_types=1);

namespace Pfinalclub\AsyncioDebug\Exporter;

use Pfinalclub\AsyncioDebug\Contract\DebugEvent;

/**
 * 空导出器
 * 
 * 丢弃所有事件的导出器实现，符合"可丢弃"原则。
 * 这是 DebugRuntime 的默认导出器，确保即使在启用调试状态下也不会影响性能。
 * 
 * 使用场景：
 * - 默认状态: DebugRuntime 初始使用
 * - 生产环境: 确保调试功能不影响性能
 * - 测试环境: 验证事件发射逻辑而不关心输出
 * - 紧急禁用: 快速禁用所有事件输出
 * 
 * 特点：
 * - 零性能开销
 * - 零内存占用
 * - 零资源消耗
 * - 永远不会失败
 * 
 * 这是一个 Null Object 模式的实现，提供了"什么都不做"的安全默认行为。
 */
final class NullExporter implements ExporterInterface
{
    /**
     * 导出事件（空实现）
     * 
     * 接收所有事件但直接丢弃，不进行任何处理。
     * 这种设计确保了：
     * 1. 调试代码的生产环境安全性
     * 2. 零性能影响的调试能力
     * 3. 可随时启用的调试基础设施
     * 
     * @param iterable<DebugEvent> $events 要导出的事件集合（会被忽略）
     * @return void
     */
    public function export(iterable $events): void
    {
        // 什么都不做 - 丢弃所有事件
        // 这是实现"零侵入"原则的关键
    }
}