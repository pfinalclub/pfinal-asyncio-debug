<?php

declare(strict_types=1);

require_once __DIR__ . '/../debug_autoload.php';

use Pfinalclub\AsyncioDebug\Asyncio\AsyncioBridge;
use Pfinalclub\AsyncioDebug\Debug\DebugRuntime;
use Pfinalclub\AsyncioDebug\Debug\FiberTracker;
use Pfinalclub\AsyncioDebug\Exporter\LogExporter;
use Pfinalclub\AsyncioDebug\Support\IdGenerator;

// 重置 ID 生成器
IdGenerator::reset();

// 启用调试运行时
DebugRuntime::enableRuntime();
DebugRuntime::getInstance()->setExporter(new LogExporter());

echo "Asyncio Debug Task 示例\n";
echo "======================\n\n";

// 获取桥接器
$bridge = AsyncioBridge::getInstance();
$tracker = new FiberTracker();

// 模拟 Task 提交
echo "1. 提交异步任务\n";
$task1Id = $bridge->onTaskSubmitted(fn() => 'Hello World');
$task2Id = $bridge->onTaskSubmitted(fn() => 42);
echo "✓ Task #{$task1Id} 已提交\n";
echo "✓ Task #{$task2Id} 已提交\n";

echo "\n2. 任务开始执行\n";
$fiberId1 = $tracker->onFiberCreated();
$fiberId2 = $tracker->onFiberCreated();

$bridge->onTaskStarted($task1Id);
$bridge->onTaskStarted($task2Id);

$tracker->onFiberStarted($fiberId1);
$tracker->onFiberStarted($fiberId2);
echo "✓ Task #{$task1Id} (Fiber #{$fiberId1}) 开始执行\n";
echo "✓ Task #{$task2Id} (Fiber #{$fiberId2}) 开始执行\n";

echo "\n3. 模拟 await 操作\n";
$tracker->onFiberSuspended($fiberId1, 'await Http::get()');
$bridge->onAwaitEnter($fiberId1, 'Http::get()');
echo "✓ Fiber #{$fiberId1} 进入等待 Http::get()\n";

sleep(1); // 模拟网络等待

$bridge->onAwaitExit($fiberId1, 'Http::get()');
$tracker->onFiberResumed($fiberId1);
echo "✓ Fiber #{$fiberId1} 完成等待 Http::get()\n";

echo "\n4. 任务完成\n";
$bridge->onTaskCompleted($task1Id, 'Response data');
$bridge->onTaskCompleted($task2Id, 42);

$tracker->onFiberTerminated($fiberId1);
$tracker->onFiberTerminated($fiberId2);
echo "✓ Task #{$task1Id} 完成，结果: string\n";
echo "✓ Task #{$task2Id} 完成，结果: integer\n";

echo "\n5. 模拟任务失败\n";
$task3Id = $bridge->onTaskSubmitted(fn() => throw new \Exception('模拟错误'));
$fiberId3 = $tracker->onFiberCreated();

$bridge->onTaskStarted($task3Id);
$tracker->onFiberStarted($fiberId3);
echo "✓ Task #{$task3Id} (Fiber #{$fiberId3}) 开始执行\n";

$bridge->onTaskFailed($task3Id, new \Exception('模拟错误'));
$tracker->onFiberTerminated($fiberId3);
echo "✓ Task #{$task3Id} 失败: 模拟错误\n";

echo "\n6. 事件循环状态\n";
for ($i = 0; $i < 3; $i++) {
    $bridge->onLoopTick();
    echo "✓ 事件循环 Tick #" . ($i + 1) . "\n";
    usleep(100000); // 100ms
}

echo "\n当前跟踪的 Fiber 数量: " . $tracker->getTrackedFiberCount() . "\n";
echo "\n示例完成！\n";