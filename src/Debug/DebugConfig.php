<?php

declare(strict_types=1);

namespace Pfinalclub\AsyncioDebug\Debug;

/**
 * 调试配置类
 * 
 * 提供统一的配置管理机制，支持运行时配置调整。
 * 这是 asyncio-debug 的配置中心，所有配置选项都集中管理。
 * 
 * 设计原则：
 * - 不可变对象，确保配置一致性
 * - 支持默认值和自定义配置
 * - 类型安全，所有配置都有明确的类型
 * - 易于扩展，支持新的配置选项
 * 
 * 使用示例：
 * ```php
 * // 使用默认配置
 * $config = DebugConfig::default();
 * 
 * // 自定义配置
 * $config = new DebugConfig(
 *     bufferSize: 500,
 *     logLevel: 'warning',
 *     enableSampling: true
 * );
 * 
 * // 创建带自定义配置的调试运行时
 * $runtime = DebugRuntime::createWithConfig($config);
 * ```
 */
final readonly class DebugConfig
{
    /**
     * 事件缓冲区大小
     * 
     * 控制事件流中保留的事件数量。
     * 缓冲区满后，最旧的事件会被覆盖。
     * 
     * 建议值：
     * - 开发环境: 1000-5000
     * - 生产环境: 100-500  
     * - 测试环境: 100-1000
     * 
     * @var int 缓冲区大小
     */
    public int $bufferSize;
    
    /**
     * 日志级别
     * 
     * 控制日志输出的详细程度。
     * 支持常见的日志级别：debug, info, warning, error
     * 
     * @var string 日志级别
     */
    public string $logLevel;
    
    /**
     * 是否启用事件采样
     * 
     * 在高负载环境下，可以启用采样来减少性能影响。
     * 采样率由采样配置决定。
     * 
     * @var bool 是否启用采样
     */
    public bool $enableSampling;
    
    /**
     * 采样率
     * 
     * 当启用采样时，控制事件采样的比例。
     * 例如 0.1 表示只记录 10% 的事件。
     * 
     * @var float 采样率，范围 0.0 到 1.0
     */
    public float $samplingRate;
    
    /**
     * 是否启用性能监控
     * 
     * 控制是否收集性能相关的指标。
     * 启用后会增加一些性能开销，但提供更详细的监控数据。
     * 
     * @var bool 是否启用性能监控
     */
    public bool $enablePerformanceMonitoring;
    
    /**
     * 是否启用详细错误报告
     * 
     * 控制错误报告的详细程度。
     * 启用后会包含更多调试信息，但可能暴露敏感数据。
     * 
     * @var bool 是否启用详细错误报告
     */
    public bool $enableVerboseErrorReporting;
    
    /**
     * 构造函数
     * 
     * @param int $bufferSize 缓冲区大小，默认 1000
     * @param string $logLevel 日志级别，默认 'info'
     * @param bool $enableSampling 是否启用采样，默认 false
     * @param float $samplingRate 采样率，默认 0.1
     * @param bool $enablePerformanceMonitoring 是否启用性能监控，默认 false
     * @param bool $enableVerboseErrorReporting 是否启用详细错误报告，默认 false
     */
    public function __construct(
        int $bufferSize = 1000,
        string $logLevel = 'info',
        bool $enableSampling = false,
        float $samplingRate = 0.1,
        bool $enablePerformanceMonitoring = false,
        bool $enableVerboseErrorReporting = false
    ) {
        // 参数验证
        if ($bufferSize <= 0) {
            throw new \InvalidArgumentException('缓冲区大小必须为正数');
        }
        
        if (!in_array($logLevel, ['debug', 'info', 'warning', 'error'], true)) {
            throw new \InvalidArgumentException('无效的日志级别');
        }
        
        if ($samplingRate < 0.0 || $samplingRate > 1.0) {
            throw new \InvalidArgumentException('采样率必须在 0.0 到 1.0 之间');
        }
        
        $this->bufferSize = $bufferSize;
        $this->logLevel = $logLevel;
        $this->enableSampling = $enableSampling;
        $this->samplingRate = $samplingRate;
        $this->enablePerformanceMonitoring = $enablePerformanceMonitoring;
        $this->enableVerboseErrorReporting = $enableVerboseErrorReporting;
    }
    
    /**
     * 创建开发环境配置
     * 
     * 适合开发调试的配置，提供最详细的信息。
     * 
     * @return self 开发环境配置
     */
    public static function forDevelopment(): self
    {
        return new self(
            bufferSize: 5000,
            logLevel: 'debug',
            enableSampling: false,
            enablePerformanceMonitoring: true,
            enableVerboseErrorReporting: true
        );
    }
    
    /**
     * 创建生产环境配置
     * 
     * 适合生产环境的配置，平衡性能和监控需求。
     * 
     * @return self 生产环境配置
     */
    public static function forProduction(): self
    {
        return new self(
            bufferSize: 500,
            logLevel: 'warning',
            enableSampling: true,
            samplingRate: 0.1,
            enablePerformanceMonitoring: false,
            enableVerboseErrorReporting: false
        );
    }
    
    /**
     * 创建测试环境配置
     * 
     * 适合测试环境的配置，提供足够的调试信息。
     * 
     * @return self 测试环境配置
     */
    public static function forTesting(): self
    {
        return new self(
            bufferSize: 1000,
            logLevel: 'info',
            enableSampling: false,
            enablePerformanceMonitoring: true,
            enableVerboseErrorReporting: true
        );
    }
    
    /**
     * 创建默认配置
     * 
     * 返回默认的配置实例。
     * 
     * @return self 默认配置
     */
    public static function default(): self
    {
        return new self();
    }
    
    /**
     * 转换为数组格式
     * 
     * 用于序列化、日志输出或配置验证。
     * 
     * @return array 配置数据的数组表示
     */
    public function toArray(): array
    {
        return [
            'buffer_size' => $this->bufferSize,
            'log_level' => $this->logLevel,
            'enable_sampling' => $this->enableSampling,
            'sampling_rate' => $this->samplingRate,
            'enable_performance_monitoring' => $this->enablePerformanceMonitoring,
            'enable_verbose_error_reporting' => $this->enableVerboseErrorReporting,
        ];
    }
    
    /**
     * 检查是否为开发配置
     * 
     * @return bool 如果是开发配置返回 true
     */
    public function isDevelopment(): bool
    {
        return $this->bufferSize >= 5000 && $this->logLevel === 'debug';
    }
    
    /**
     * 检查是否为生产配置
     * 
     * @return bool 如果是生产配置返回 true
     */
    public function isProduction(): bool
    {
        return $this->bufferSize <= 500 && $this->enableSampling;
    }
}