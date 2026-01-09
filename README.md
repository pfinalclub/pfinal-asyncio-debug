# asyncio-debug

**asyncio-debug 不是调试工具，它是异步运行时的"事实记录器"。**

## 核心定位

- **零侵入** - 不改变 pfinal-asyncio 的调度、执行、性能特性
- **低心智负担** - 事件是"事实"，不是"观点"  
- **可丢弃** - 任何时刻可以整体关闭，不影响业务
- **可外接** - 能被 APM、日志系统、Profiler 消费

## 架构总览

```
asyncio-debug
├── Contract/     # 稳定的事件协议
├── Debug/        # 运行时绑定与控制
├── Asyncio/      # 与 pfinal-asyncio 的桥
├── Event/        # 事件流（不解释）
├── Metrics/      # 原始计数器
├── Exporter/     # 输出边界
└── Support/      # 基础工具
```

## 快速开始

### 基础使用

```php
<?php

require_once 'debug_autoload.php';

use Pfinalclub\AsyncioDebug\Debug\DebugRuntime;
use Pfinalclub\AsyncioDebug\Debug\FiberTracker;
use Pfinalclub\AsyncioDebug\Exporter\LogExporter;

// 启用调试运行时
DebugRuntime::enableRuntime();

// 设置日志导出器
DebugRuntime::getInstance()->setExporter(new LogExporter());

// 创建 Fiber 跟踪器
$tracker = new FiberTracker();

// 模拟 Fiber 生命周期
$fiberId = $tracker->onFiberCreated();
$tracker->onFiberStarted($fiberId);
$tracker->onFiberSuspended($fiberId, '等待 I/O');
$tracker->onFiberResumed($fiberId);
$tracker->onFiberTerminated($fiberId);
```

### Task 调试

```php
use Pfinalclub\AsyncioDebug\Asyncio\AsyncioBridge;

$bridge = AsyncioBridge::getInstance();

// 任务生命周期
$taskId = $bridge->onTaskSubmitted(fn() => 'Hello');
$bridge->onTaskStarted($taskId);
$bridge->onTaskCompleted($taskId, 'Hello World');

// Await 跟踪
$bridge->onAwaitEnter($fiberId, 'Http::get()');
// ... 等待操作 ...
$bridge->onAwaitExit($fiberId, 'Http::get()');
```

## 事件类型

```php
enum EventType: string {
    case FIBER_CREATED;
    case FIBER_STARTED;
    case FIBER_SUSPENDED;
    case FIBER_RESUMED;
    case FIBER_TERMINATED;

    case TASK_SUBMITTED;
    case TASK_STARTED;
    case TASK_COMPLETED;
    case TASK_FAILED;

    case AWAIT_ENTER;
    case AWAIT_EXIT;

    case LOOP_TICK;
}
```

## 导出器

### LogExporter - 输出到日志
```php
$exporter = new LogExporter();
DebugRuntime::getInstance()->setExporter($exporter);
```

### NullExporter - 丢弃所有事件
```php
$exporter = new NullExporter();
DebugRuntime::getInstance()->setExporter($exporter);
```

## 示例

运行示例代码：

```bash
php examples/basic_usage.php     # 基础 Fiber 生命周期示例
php examples/async_tasks.php      # 异步任务调试示例
php tests/DebugRuntimeTest.php    # 运行单元测试
```

## 设计原则

1. **Observer-only（只观察，不干预）** - 永远不参与调度
2. **事件驱动，而非侵入式调用** - 禁止反向依赖
3. **语义优先于性能指标** - 关注「await 在哪」而不是「CPU 使用率」

## 不做列表

❌ 自动 kill Task
❌ 自动调度优化  
❌ 自动 GC / cleanup
❌ 替代 APM
❌ UI 强绑定

---

**asyncio-debug 是 pfinal-asyncio 的运行时观测工具，用于解释异步行为，而不是改变异步行为。**