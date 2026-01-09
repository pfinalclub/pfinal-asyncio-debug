<?php

declare(strict_types=1);

require_once __DIR__ . '/../debug_autoload.php';

use Pfinalclub\AsyncioDebug\Debug\DebugRuntime;
use Pfinalclub\AsyncioDebug\Debug\FiberTracker;
use Pfinalclub\AsyncioDebug\Exporter\LogExporter;
use Pfinalclub\AsyncioDebug\Support\IdGenerator;

// 重置 ID 生成器
IdGenerator::reset();

// 启用调试运行时
DebugRuntime::enableRuntime();

// 设置日志导出器
DebugRuntime::getInstance()->setExporter(new LogExporter());

echo "Asyncio Debug 基础示例\n";
echo "====================\n\n";

// 创建 Fiber 跟踪器
$tracker = new FiberTracker();

// 模拟 Fiber 生命周期
echo "模拟 Fiber 生命周期...\n";

$fiberId = $tracker->onFiberCreated();
echo "✓ Fiber 创建: ID $fiberId\n";

$tracker->onFiberStarted($fiberId);
echo "✓ Fiber 启动\n";

usleep(50000); // 模拟一些工作

$tracker->onFiberSuspended($fiberId, '等待 I/O');
echo "✓ Fiber 暂停: 等待 I/O\n";

usleep(100000); // 模拟 I/O 等待

$tracker->onFiberResumed($fiberId);
echo "✓ Fiber 恢复\n";

usleep(50000); // 继续工作

$tracker->onFiberTerminated($fiberId);
echo "✓ Fiber 终止\n";

echo "\n当前跟踪的 Fiber 数量: " . $tracker->getTrackedFiberCount() . "\n";

echo "\n禁用调试运行时...\n";
DebugRuntime::disableRuntime();

echo "\n示例完成！\n";