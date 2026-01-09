<?php

declare(strict_types=1);

namespace Pfinalclub\AsyncioDebug\Debug;

use Pfinalclub\AsyncioDebug\Contract\DebugEvent;
use Pfinalclub\AsyncioDebug\Contract\EventEmitterInterface;
use Pfinalclub\AsyncioDebug\Exporter\NullExporter;
use Pfinalclub\AsyncioDebug\Exporter\ExporterInterface;

/**
 * 调试运行时核心类
 * 
 * 这是 asyncio-debug 的核心控制器，负责：
 * 1. 全局调试开关管理
 * 2. 事件发射的统一入口
 * 3. 导出器的生命周期管理
 * 
 * 设计特点：
 * - 单例模式，确保全局唯一的调试状态
 * - 默认禁用，符合"零侵入"原则
 * - 支持运行时启用/禁用
 * - 支持替换导出器
 * 
 * 使用模式：
 * ```php
 * // 启用调试
 * DebugRuntime::enableRuntime();
 * 
 * // 设置导出器
 * DebugRuntime::getInstance()->setExporter(new LogExporter());
 * 
 * // 发射事件
 * DebugRuntime::emitEvent($event);
 * 
 * // 禁用调试
 * DebugRuntime::disableRuntime();
 * ```
 */
final class DebugRuntime implements EventEmitterInterface
{
    /**
     * 单例实例
     * 
     * 保证全局只有一个调试运行时实例，
     * 避免状态混乱和资源浪费
     * 
     * @var self|null 单例实例
     */
    private static ?self $instance = null;

    /**
     * 调试开关状态
     * 
     * 控制是否启用调试功能。
     * 默认为 false，确保不影响正常业务。
     * 
     * @var bool 是否启用调试
     */
    private bool $enabled = false;

    /**
     * 事件导出器
     * 
     * 负责将事件输出到目标位置（日志、文件、监控系统等）。
     * 默认使用 NullExporter，丢弃所有事件。
     * 
     * @var ExporterInterface 事件导出器实例
     */
    private ExporterInterface $exporter;

    /**
     * 私有构造函数
     * 
     * 防止外部直接实例化，确保单例模式。
     * 初始化时使用 NullExporter，符合"默认禁用"原则。
     */
    private function __construct()
    {
        $this->exporter = new NullExporter();
    }

    /**
     * 获取单例实例
     * 
     * @return self 调试运行时实例
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * 检查是否启用调试
     * 
     * @return bool 如果启用返回 true，否则返回 false
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * 启用调试功能
     * 
     * 启用后，emit() 方法会将事件发送到导出器。
     * 这是一个运行时开关，不需要重启应用。
     * 
     * @return void
     */
    public function enable(): void
    {
        $this->enabled = true;
    }

    /**
     * 禁用调试功能
     * 
     * 禁用后，emit() 方法将静默忽略所有事件。
     * 这是实现"可丢弃"原则的关键方法。
     * 
     * @return void
     */
    public function disable(): void
    {
        $this->enabled = false;
    }

    /**
     * 设置事件导出器
     * 
     * 替换当前的事件导出器，可以动态改变事件输出目标。
     * 例如：从 LogExporter 切换到 FileExporter。
     * 
     * @param ExporterInterface $exporter 新的导出器实例
     * @return void
     */
    public function setExporter(ExporterInterface $exporter): void
    {
        $this->exporter = $exporter;
    }

    /**
     * 发射调试事件
     * 
     * 实现 EventEmitterInterface 接口的核心方法。
     * 只有在启用状态下才会将事件发送到导出器。
     * 
     * @param DebugEvent $event 要发射的调试事件
     * @return void
     */
    public function emit(DebugEvent $event): void
    {
        // 如果未启用，静默忽略事件
        if (!$this->enabled) {
            return;
        }

        // 将事件发送到导出器
        $this->exporter->export([$event]);
    }

    /**
     * 静态方法：发射事件
     * 
     * 便捷的静态方法，不需要获取实例即可发射事件。
     * 这是最常用的事件发射方式。
     * 
     * @param DebugEvent $event 要发射的调试事件
     * @return void
     */
    public static function emitEvent(DebugEvent $event): void
    {
        self::getInstance()->emit($event);
    }

    /**
     * 静态方法：启用调试运行时
     * 
     * 便捷的静态方法，不需要获取实例即可启用调试。
     * 
     * @return void
     */
    public static function enableRuntime(): void
    {
        self::getInstance()->enable();
    }

    /**
     * 静态方法：禁用调试运行时
     * 
     * 便捷的静态方法，不需要获取实例即可禁用调试。
     * 
     * @return void
     */
    public static function disableRuntime(): void
    {
        self::getInstance()->disable();
    }

    /**
     * 静态方法：检查运行时是否启用
     * 
     * 便捷的静态方法，不需要获取实例即可检查状态。
     * 
     * @return bool 如果启用返回 true，否则返回 false
     */
    public static function runtimeEnabled(): bool
    {
        return self::getInstance()->isEnabled();
    }
}