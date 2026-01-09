<?php

declare(strict_types=1);

namespace Pfinalclub\AsyncioDebug\Asyncio;

use Pfinalclub\AsyncioDebug\Contract\DebugEvent;
use Pfinalclub\AsyncioDebug\Contract\EventType;
use Pfinalclub\AsyncioDebug\Debug\DebugRuntime;
use Pfinalclub\AsyncioDebug\Support\IdGenerator;

final class AsyncioBridge
{
    private static ?self $instance = null;

    private function __construct()
    {
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function onTaskSubmitted(callable $task): int
    {
        $taskId = IdGenerator::generateTaskId();
        
        $event = DebugEvent::create(
            EventType::TASK_SUBMITTED,
            0, // Will be updated when fiber is created
            $taskId,
            [
                'task_type' => 'callable',
                'submitted_at' => microtime(true)
            ]
        );

        DebugRuntime::emitEvent($event);

        return $taskId;
    }

    public function onTaskStarted(int $taskId): void
    {
        $event = DebugEvent::create(
            EventType::TASK_STARTED,
            0,
            $taskId,
            ['started_at' => microtime(true)]
        );

        DebugRuntime::emitEvent($event);
    }

    public function onTaskCompleted(int $taskId, mixed $result = null): void
    {
        $event = DebugEvent::create(
            EventType::TASK_COMPLETED,
            0,
            $taskId,
            [
                'completed_at' => microtime(true),
                'result_type' => gettype($result)
            ]
        );

        DebugRuntime::emitEvent($event);
    }

    public function onTaskFailed(int $taskId, \Throwable $error): void
    {
        $event = DebugEvent::create(
            EventType::TASK_FAILED,
            0,
            $taskId,
            [
                'failed_at' => microtime(true),
                'error_type' => get_class($error),
                'error_message' => $error->getMessage()
            ]
        );

        DebugRuntime::emitEvent($event);
    }

    public function onAwaitEnter(int $fiberId, string $awaitTarget): void
    {
        $event = DebugEvent::create(
            EventType::AWAIT_ENTER,
            $fiberId,
            null,
            [
                'await_target' => $awaitTarget,
                'await_enter_at' => microtime(true)
            ]
        );

        DebugRuntime::emitEvent($event);
    }

    public function onAwaitExit(int $fiberId, string $awaitTarget): void
    {
        $event = DebugEvent::create(
            EventType::AWAIT_EXIT,
            $fiberId,
            null,
            [
                'await_target' => $awaitTarget,
                'await_exit_at' => microtime(true)
            ]
        );

        DebugRuntime::emitEvent($event);
    }

    public function onLoopTick(): void
    {
        $event = DebugEvent::create(
            EventType::LOOP_TICK,
            0,
            null,
            ['tick_at' => microtime(true)]
        );

        DebugRuntime::emitEvent($event);
    }
}