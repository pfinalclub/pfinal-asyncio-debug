<?php

declare(strict_types=1);

namespace Pfinalclub\AsyncioDebug\Exporter;

use Pfinalclub\AsyncioDebug\Contract\DebugEvent;

/**
 * 事件导出器接口
 * 
 * 定义了事件导出器必须实现的基本功能。
 * 导出器负责将调试事件输出到各种目标：日志文件、监控系统、数据库等。
 * 
 * 设计原则：
 * - 简单接口，易于实现
 * - 支持批量导出，提高效率
 * - 类型安全，明确参数类型
 * - 异常处理由实现类负责
 * 
 * 实现类责任：
 * - 决定是否采样事件
 * - 决定是否丢弃事件  
 * - 决定批量处理策略
 * - 处理导出失败的情况
 * 
 * asyncio-debug 不关心：
 * - 事件的具体格式
 * - 导出的目标位置
 * - 导出的性能表现
 * - 失败重试机制
 * 
 * 实现示例：
 * - LogExporter: 输出到日志
 * - FileExporter: 写入文件
 * - HttpExporter: 发送到 HTTP API
 * - PrometheusExporter: 导出为 Prometheus 格式
 */
interface ExporterInterface
{
    /**
     * 导出事件
     * 
     * 将一批调试事件导出到目标位置。
     * 导出器应该能够处理空事件列表。
     * 
     * @param iterable<DebugEvent> $events 要导出的事件集合
     * @return void
     * @throws \Exception 当导出失败时可以抛出异常
     */
    public function export(iterable $events): void;
}