<?php

declare(strict_types=1);

namespace Ep\Swoole;

use ReflectionClass;

final class SwooleEvent
{
    const ON_START = 'start';

    const ON_SHUTDOWN = 'shutdown';

    const ON_WORKER_START = 'workerStart';

    const ON_WORKER_STOP = 'workerStop';

    const ON_WORKER_EXIT = 'workerExit';

    const ON_CONNECT = 'connect';

    const ON_RECEIVE = 'receive';

    const ON_PACKET = 'packet';

    const ON_CLOSE = 'close';

    const ON_TASK = 'task';

    const ON_FINISH = 'finish';

    const ON_PIPE_MESSAGE = 'pipeMessage';

    const ON_WORKER_ERROR = 'workerError';

    const ON_MANAGER_START = 'managerStart';

    const ON_MANAGER_STOP = 'managerStop';

    const ON_BEFORE_RELOAD = 'beforeReload';

    const ON_AFTER_RELOAD = 'afterReload';
    /**
     * WebSocket Event.
     */
    const ON_HAND_SHAKE = 'handshake';
    /**
     * WebSocket Event.
     */
    const ON_OPEN = 'open';
    /**
     * WebSocket Event.
     */
    const ON_MESSAGE = 'message';
    /**
     * Http Event.
     */
    const ON_REQUEST = 'request';

    public static function isSwooleEvent($event): bool
    {
        $consts = (new ReflectionClass(self::class))->getConstants();
        return in_array($event, $consts);
    }
}
