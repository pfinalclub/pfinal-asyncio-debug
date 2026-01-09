<?php

declare(strict_types=1);

namespace Pfinalclub\AsyncioDebug\Contract;

/**
 * 事件载荷容器
 * 
 * 用于存储事件的详细数据，提供类型安全的数据访问方法。
 * 
 * 设计原则：
 * - 只存储事实数据，不包含解释性信息
 * - 不存储大对象或闭包，避免内存泄漏
 * - 支持结构化数据查询
 * 
 * 使用示例：
 * ```php
 * $payload = EventPayload::create([
 *     'await_target' => 'Http::get()',
 *     'duration_ms' => 150
 * ]);
 * 
 * $target = $payload->get('await_target');
 * $hasDuration = $payload->has('duration_ms');
 * ```
 */
final class EventPayload
{
    /**
     * 私有构造函数，强制使用工厂方法创建实例
     * 
     * @param array $data 事件数据数组
     */
    private function __construct(
        private readonly array $data
    ) {
    }

    /**
     * 创建事件载荷实例
     * 
     * @param array $data 事件数据数组
     * @return self 新的事件载荷实例
     */
    public static function create(array $data): self
    {
        return new self($data);
    }

    /**
     * 获取指定键的值
     * 
     * @param string $key 数据键名
     * @param mixed $default 默认值（当键不存在时返回）
     * @return mixed 对应的值或默认值
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * 检查是否存在指定的键
     * 
     * @param string $key 要检查的键名
     * @return bool 如果键存在返回 true，否则返回 false
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * 获取所有数据
     * 
     * @return array 完整的数据数组
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * 检查载荷是否为空
     * 
     * @return bool 如果没有数据返回 true，否则返回 false
     */
    public function isEmpty(): bool
    {
        return empty($this->data);
    }
}