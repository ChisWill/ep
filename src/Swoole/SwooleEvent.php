<?php

declare(strict_types=1);

namespace Ep\Swoole;

use ReflectionClass;

final class SwooleEvent
{
    public const ON_START = 'start';

    public const ON_SHUTDOWN = 'shutdown';

    public const ON_WORKER_START = 'workerStart';

    public const ON_WORKER_STOP = 'workerStop';

    public const ON_WORKER_EXIT = 'workerExit';

    public const ON_CONNECT = 'connect';

    public const ON_RECEIVE = 'receive';

    public const ON_PACKET = 'packet';

    public const ON_CLOSE = 'close';

    public const ON_TASK = 'task';

    public const ON_FINISH = 'finish';

    public const ON_PIPE_MESSAGE = 'pipeMessage';

    public const ON_WORKER_ERROR = 'workerError';

    public const ON_MANAGER_START = 'managerStart';

    public const ON_MANAGER_STOP = 'managerStop';

    public const ON_BEFORE_RELOAD = 'beforeReload';

    public const ON_AFTER_RELOAD = 'afterReload';
    /**
     * WebSocket Event.
     */
    public const ON_HAND_SHAKE = 'handshake';
    /**
     * WebSocket Event.
     */
    public const ON_OPEN = 'open';
    /**
     * WebSocket Event.
     */
    public const ON_MESSAGE = 'message';
    /**
     * Http Event.
     */
    public const ON_REQUEST = 'request';

    public static function isSwooleEvent($event): bool
    {
        $consts = (new ReflectionClass(self::class))->getConstants();
        return in_array($event, $consts);
    }
}
