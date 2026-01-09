# asyncio-debug（精简版）完整设计

> asyncio-debug 不是调试工具
> 它是 **异步运行时的“事实记录器”**

---

## 一、核心定位（一句话）

**asyncio-debug 负责：记录异步运行时发生了什么，而不是解释它意味着什么。**

* 不做诊断
* 不做分析
* 不做智能判断
* 不做 UI 假设

所有“高级东西”，都交给下游。

---

## 二、设计目标（砍完后的真实目标）

1. **零侵入**
   不改变 pfinal-asyncio 的调度、执行、性能特性

2. **低心智负担**
   事件是“事实”，不是“观点”

3. **可丢弃**
   任何时刻可以整体关闭，不影响业务

4. **可外接**
   能被 APM、日志系统、Profiler 消费

---

## 三、架构总览（精简后）

```text
asyncio-debug
│
├── Debug/        # 运行时绑定与控制
├── Contract/     # 稳定的事件协议
├── Asyncio/      # 与 pfinal-asyncio 的桥
├── Event/        # 事件流（不解释）
├── Metrics/      # 原始计数器
├── Exporter/     # 输出边界
└── Support/      # 基础工具
```

---

## 四、模块职责（逐个讲清楚）

### 1️⃣ Debug（运行时入口）

```text
Debug/
├── DebugRuntime.php
├── FiberTracker.php
└── DebugBootstrap.php
```

#### DebugRuntime

* 全局调试开关
* 控制是否启用事件发射
* 管理 Exporter 生命周期

```php
DebugRuntime::enable();
DebugRuntime::disable();
DebugRuntime::emit(DebugEvent $event);
```

#### FiberTracker

* 仅记录 Fiber 的**事实状态**
* 不推断“阻塞 / 慢 / 异常”

```php
onFiberCreated()
onFiberStarted()
onFiberSuspended()
onFiberTerminated()
```

#### DebugBootstrap

* 与框架集成（CLI / Workerman / Webman）
* 自动挂载 AsyncioBridge

---

### 2️⃣ Contract（最重要，最不能乱）

```text
Contract/
├── DebugEvent.php
├── EventType.php
├── EventPayload.php
└── EventEmitterInterface.php
```

#### EventType（稳定枚举）

```php
enum EventType: string {
    case FIBER_CREATED;
    case FIBER_STARTED;
    case FIBER_SUSPENDED;
    case FIBER_RESUMED;
    case FIBER_TERMINATED;

    case TASK_SUBMITTED;
    case TASK_COMPLETED;
    case TASK_FAILED;

    case AWAIT_ENTER;
    case AWAIT_EXIT;

    case LOOP_TICK;
}
```

> ⚠️ 这个枚举一旦发布，就是 **兼容性承诺**

#### DebugEvent

```php
final class DebugEvent {
    public EventType $type;
    public float $timestamp;
    public int $fiberId;
    public array $payload;
}
```

* payload 永远是 **事实字段**
* 不允许自然语言

---

### 3️⃣ Asyncio（与 pfinal-asyncio 的边界）

```text
Asyncio/
├── AsyncioBridge.php
├── TaskInspector.php
└── AwaitTracker.php
```

#### AsyncioBridge

* 监听 EventLoop / Scheduler
* 把内部状态翻译成 DebugEvent

> ⚠️ 这是 **唯一** 允许了解 asyncio 内部的地方

#### TaskInspector

* 只读取 task 状态
* 不缓存，不持有 task 引用

#### AwaitTracker

* 记录 await 进入 / 退出
* 只记录“发生了等待”

---

### 4️⃣ Event（事件流，不是时间线）

```text
Event/
└── EventStream.php
```

```php
class EventStream {
    public function push(DebugEvent $event): void;
    public function flush(): iterable;
}
```

* 保证顺序
* 不保证完整
* 不保证持久

---

### 5️⃣ Metrics（原始计数）

```text
Metrics/
├── Counter.php
└── Gauge.php
```

#### Counter

```php
$fiberCreated->inc();
```

#### Gauge

```php
$activeFibers->set(12);
```

> ❌ 没有 Snapshot
> ❌ 没有 Aggregation
> ❌ 没有 “平均值”

---

### 6️⃣ Exporter（唯一对外接口）

```text
Exporter/
├── ExporterInterface.php
├── LogExporter.php
├── JsonExporter.php
└── NullExporter.php
```

#### ExporterInterface

```php
interface ExporterInterface {
    public function export(iterable $events): void;
}
```

* Exporter 决定：

  * 是否采样
  * 是否丢事件
  * 是否批量

asyncio-debug **不关心**

---

### 7️⃣ Support（工具，不带语义）

```text
Support/
├── RingBuffer.php
└── IdGenerator.php
```

* RingBuffer：防 OOM
* IdGenerator：fiber / task ID

---

## 五、运行时生命周期（重要）

```text
pfinal-asyncio
    │
    ├─ 创建 Fiber
    │    └─ emit FIBER_CREATED
    │
    ├─ 调度执行
    │    ├─ emit FIBER_STARTED
    │    ├─ emit AWAIT_ENTER
    │    ├─ emit AWAIT_EXIT
    │    └─ emit FIBER_SUSPENDED
    │
    └─ 结束
         └─ emit FIBER_TERMINATED
```

👉 asyncio-debug **从不干预这条路径**

---

## 六、和 pfinal-asyncio 的关系（边界声明）

| 方面           | asyncio-debug |
| ------------ | ------------- |
| 调度           | ❌ 不参与         |
| 性能优化         | ❌ 不干预         |
| Fiber 生命周期   | ✅ 只观察         |
| 内存管理         | ❌ 不接管         |
| GC / Cleanup | ❌ 不碰          |

> **debug 永远是旁观者**

---

## 七、为什么这套设计能活 5 年

说实话：

* 你砍掉的是“现在看起来牛逼，未来一定背锅”的部分
* 留下的是 **最底层、最诚实、最不可替代的东西**

这套设计有三个天然优势：

1. **不会被 Swoole / Swow / Fibers 演进击穿**
2. **不会和 asyncio-http / 微服务 / RPC 耦合**
3. **不会绑死任何可视化方案**
