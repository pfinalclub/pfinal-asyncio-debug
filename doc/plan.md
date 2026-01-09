# asyncio-debug 架构规划

## 1. 设计背景与目标

### 背景问题

在 pfinal-asyncio 引入 Fiber、Task、await / gather 等异步语义之后，出现了新的可观测性问题：

* 异步任务为何变慢？
* Fiber 是否泄漏？
* await 链路中谁在阻塞？
* 调度是否公平？
* 清理策略是否生效？

这些问题**无法**通过传统工具（xdebug、log、APM）完整回答。

### 核心目标

> **asyncio-debug 的目标不是优化性能，而是解释 async 行为。**

具体目标：

* 可解释：让 async 行为“看得见”
* 低侵入：不影响调度与语义
* 可组合：不绑定 UI / 存储 / 监控系统
* 可降级：在非 asyncio 环境下仍可部分工作

---

## 2. 总体设计原则（不可破坏）

### 原则 1：Observer-only（只观察，不干预）

* asyncio-debug **永远不参与调度**
* 不修改 Task / Fiber 行为
* 不影响清理、优先级、事件循环

> 调试工具一旦开始“聪明”，系统就开始变得不可预测

---

### 原则 2：事件驱动，而非侵入式调用

* core 通过 `emit(event)` 暴露事实
* debug 通过 `subscribe(event)` 消费事实
* 禁止反向依赖

---

### 原则 3：语义优先于性能指标

* 关注「await 在哪」
* 而不是「CPU 使用率」
* 不做吞吐 KPI

---

## 3. 架构总览

### 高层结构

```text
┌──────────────────────────┐
│      User Application     │
└────────────┬─────────────┘
             │
┌────────────▼─────────────┐
│     pfinal-asyncio core   │
│  (Task / Fiber / Loop)    │
└────────────┬─────────────┘
             │ emit events
┌────────────▼─────────────┐
│       asyncio-debug       │
│  (Observer / Collector)   │
└────────────┬─────────────┘
             │ export
┌────────────▼─────────────┐
│   Logger / Metrics / UI   │
└──────────────────────────┘
```

---

## 4. 模块划分（核心）

### 4.1 DebugRuntime（基础运行时层）

**职责：**

* 记录 Fiber 生命周期
* 捕获 suspend / resume
* 记录时间戳与内存快照

**特点：**

* 不依赖 asyncio core
* 可单独运行
* 不理解 Task / await

```php
DebugRuntime::onFiberSuspend(Fiber $fiber);
DebugRuntime::onFiberResume(Fiber $fiber);
```

这是 asyncio-debug 的**最小可用内核**。

---

### 4.2 AsyncioInspector（语义分析层）

**职责：**

* 理解 Task
* 解析 await / gather / race
* 构建 async 调用关系图（DAG）

**输入：**

* DebugRuntime 事件
* asyncio core 事件（task.created / awaited / completed）

**输出：**

* await 链路
* 阻塞点分析
* Task 状态快照

```text
Task #42
 ├─ await Http::get()
 │   └─ suspended 312ms
 └─ completed
```

---

### 4.3 TimelineBuilder（时间线构建）

**职责：**

* 按时间顺序整理事件
* 生成可视化友好的结构
* 支持 sampling / 截断

**注意：**

* 不保存完整对象引用
* 只保留必要 metadata（id、timestamp、type）

---

### 4.4 Snapshot & Metrics（统计层）

**职责：**

* 活跃 Task 数
* Fiber 峰值
* await 平均等待时间
* 调度频率

**明确不做：**

* CPU profiling
* memory leak 自动判断

---

## 5. 事件模型设计（关键）

### 核心事件类型

```text
task.created
task.await
task.completed
fiber.suspended
fiber.resumed
loop.tick
cleanup.triggered
```

### 事件设计原则

* 事件只陈述事实
* 不携带策略信息
* 不包含闭包 / 大对象

---

## 6. 启动与降级策略

### 启动方式

```php
AsyncioDebug::bootstrap([
    'mode' => 'auto',
]);
```

### 模式说明

| 环境             | 行为                    |
| -------------- | --------------------- |
| 有 asyncio core | 启用完整分析                |
| 无 asyncio core | 退化为 Fiber 观测          |
| CLI / Debug    | 全量记录                  |
| Production     | Sampling / RingBuffer |

---

## 7. 性能与安全边界

### 性能约束

* Debug 默认关闭
* 生产环境必须显式开启
* 支持：

  * sampling
  * buffer size limit
  * drop 策略

### 内存安全

* 禁止长期持有 Fiber / Task 实例
* 使用弱引用（WeakReference）
* 所有历史数据可被丢弃

---

## 8. 不做列表（非常重要）

asyncio-debug **明确不做**：

* ❌ 自动 kill Task
* ❌ 自动调度优化
* ❌ 自动 GC / cleanup
* ❌ 替代 APM
* ❌ UI 强绑定

---

## 9. 与生态的关系

| 模块                 | 关系      |
| ------------------ | ------- |
| pfinal-asyncio     | 强依赖（语义） |
| asyncio-http       | 被观测对象   |
| webman / workerman | 事件源     |
| 第三方监控              | 输出目标    |

---

## 10. 演进路线建议

### v0.x（MVP）

* Fiber timeline
* Task 生命周期
* 基础统计

### v1.x

* await 链路分析
* 阻塞点定位
* 简单可视化输出

### v2.x（可选）

* 事件标准化
* 外部插件（APM / UI）

---

## 11. 一句话定位（可直接用）

> **asyncio-debug 是 pfinal-asyncio 的运行时观测工具，
> 用于解释异步行为，而不是改变异步行为。**

---

## 作者级总结（实话）

你给 asyncio-debug 定下这个边界，相当于做了一件很成熟的事：

> **你拒绝把“调试工具”变成“第二套 runtime”。**

这在 async 系统里是极其罕见、也极其正确的选择。

