<?php

declare(strict_types=1);

namespace Pfinalclub\AsyncioDebug\Exception;

/**
 * 导出器异常
 * 
 * 当事件导出过程中发生错误时抛出。
 * 例如：日志写入失败、网络连接错误、文件权限问题等。
 * 
 * 使用场景：
 * - 日志文件写入失败
 * - HTTP 导出连接错误
 * - 数据库导出错误
 * - 导出器配置错误
 */
class ExporterException extends DebugException
{
    /**
     * 错误码：日志导出错误
     */
    public const CODE_LOG_EXPORT_ERROR = 3100;
    
    /**
     * 错误码：文件导出错误
     */
    public const CODE_FILE_EXPORT_ERROR = 3101;
    
    /**
     * 错误码：网络导出错误
     */
    public const CODE_NETWORK_EXPORT_ERROR = 3102;
    
    /**
     * 错误码：导出器配置错误
     */
    public const CODE_EXPORTER_CONFIG_ERROR = 3103;
    
    /**
     * 创建日志导出错误异常
     * 
     * @param string $message 错误消息
     * @param array $context 上下文信息
     * @return self
     */
    public static function logExportError(string $message, array $context = []): self
    {
        return new self($message, self::CODE_LOG_EXPORT_ERROR, null, $context);
    }
    
    /**
     * 创建文件导出错误异常
     * 
     * @param string $filename 文件名
     * @param string $error 错误信息
     * @param array $context 上下文信息
     * @return self
     */
    public static function fileExportError(string $filename, string $error, array $context = []): self
    {
        $message = sprintf('文件导出失败: %s (%s)', $filename, $error);
        return new self($message, self::CODE_FILE_EXPORT_ERROR, null, $context);
    }
    
    /**
     * 创建网络导出错误异常
     * 
     * @param string $url 目标 URL
     * @param string $error 错误信息
     * @param array $context 上下文信息
     * @return self
     */
    public static function networkExportError(string $url, string $error, array $context = []): self
    {
        $message = sprintf('网络导出失败: %s (%s)', $url, $error);
        return new self($message, self::CODE_NETWORK_EXPORT_ERROR, null, $context);
    }
    
    /**
     * 创建导出器配置错误异常
     * 
     * @param string $configKey 配置键
     * @param string $error 错误信息
     * @param array $context 上下文信息
     * @return self
     */
    public static function exporterConfigError(string $configKey, string $error, array $context = []): self
    {
        $message = sprintf('导出器配置错误: %s (%s)', $configKey, $error);
        return new self($message, self::CODE_EXPORTER_CONFIG_ERROR, null, $context);
    }
}