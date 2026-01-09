<?php

declare(strict_types=1);

namespace Pfinalclub\AsyncioDebug\Exporter;

use Pfinalclub\AsyncioDebug\Contract\DebugEvent;

/**
 * 日志导出器
 * 
 * 将调试事件输出到日志系统的导出器实现。
 * 支持自定义日志记录器，默认使用 PHP 的 error_log 函数。
 * 
 * 输出格式：
 * ```
 * [asyncio-debug] fiber.created (fiber:1, task:null) {"created_at":1234567890.123}
 * [asyncio-debug] task.completed (fiber:0, task:42) {"result_type":"string"}
 * ```
 * 
 * 使用场景：
 * - 开发调试: 快速查看事件流
 * - 问题排查: 分析异步行为
 * - 性能分析: 查看事件时间戳
 * - 监控集成: 导出到现有日志系统
 * 
 * 特点：
 * - 格式化输出，便于阅读
 * - 支持自定义日志器
 * - 结构化载荷信息
 * - 高效批量处理
 * 
 * 扩展建议：
 * - 添加日志级别控制
 * - 支持 JSON 格式输出
 * - 添加采样功能
 * - 支持异步日志写入
 */
final class LogExporter implements ExporterInterface
{


    /**
     * 构造函数
     * 
     * @param string $logLevel 日志级别，默认 'info'
     * @param mixed|null $logger 自定义日志记录器，null 使用 error_log
     */
    public function __construct(
        private readonly string $logLevel = 'info',
        private readonly mixed $logger = null
    ) {
        // 只读属性通过构造函数参数直接赋值，无需额外赋值
    }

    /**
     * 导出事件到日志
     * 
     * 遍历所有事件，将每个事件格式化为日志消息并输出。
     * 这是同步操作，可能会影响性能，适合开发环境使用。
     * 
     * @param iterable<DebugEvent> $events 要导出的事件集合
     * @return void
     * @throws \Pfinalclub\AsyncioDebug\Exception\ExporterException
     */
    public function export(iterable $events): void
    {
        try {
            foreach ($events as $event) {
                $this->logEvent($event);
            }
        } catch (\Throwable $e) {
            // 重新抛出为导出器异常，保留原始异常信息
            throw \Pfinalclub\AsyncioDebug\Exception\ExporterException::logExportError(
                sprintf('日志导出失败: %s', $e->getMessage()),
                ['exception' => get_class($e), 'code' => $e->getCode()]
            );
        }
    }

    /**
     * 记录单个事件
     * 
     * 将事件格式化为可读的日志消息。
     * 格式包含事件类型、关联的 Fiber/Task ID 和载荷信息。
     * 
     * @param DebugEvent $event 要记录的调试事件
     * @return void
     */
    private function logEvent(DebugEvent $event): void
    {
        // 转换事件为数组格式
        $data = $event->toArray();
        
        // 构建日志消息
        $message = sprintf(
            '[asyncio-debug] %s (fiber:%d, task:%s) %s',
            $data['type'],                                    // 事件类型
            $data['fiber_id'],                               // Fiber ID
            $data['task_id'] ?? 'null',                     // Task ID（可能为 null）
            json_encode($data['payload'], JSON_UNESCAPED_UNICODE) // 载荷 JSON
        );

        // 根据是否有自定义日志器选择输出方式
        if ($this->logger) {
            // 使用自定义日志器
            ($this->logger)($this->logLevel, $message);
        } else {
            // 使用默认的 error_log
            error_log($message);
        }
    }
}