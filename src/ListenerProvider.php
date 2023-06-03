<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Event;

use Max\Event\Contract\EventListenerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use SplPriorityQueue;

class ListenerProvider implements ListenerProviderInterface
{
    /**
     * @var array<class-string, EventListenerInterface[]> $events
     */
    protected array $events = [];

    /**
     * 已经注册的监听器.
     *
     * @var array<class-string, EventListenerInterface[]>
     */
    protected array $listeners = [];

    /**
     * 注册单个事件监听.
     */
    public function addListener(EventListenerInterface ...$eventListeners)
    {
        if (empty($eventListeners)) {
            return;
        }
        foreach ($eventListeners as $eventListener) {
            $listener = $eventListener::class;
            if (!$this->listened($listener)) {
                $this->listeners[$listener] = $eventListener;
                foreach ($eventListener->listen() as $event) {
                    if (!isset($this->events[$event])) {
                        $this->events[$event] = new SplPriorityQueue();
                    }
                    $this->events[$event]->insert($eventListener, $eventListener->getPriority());
                }
            }
        }
    }

    /**
     * 判断是否已经监听.
     */
    public function listened(string $listeners): bool
    {
        return isset($this->listeners[$listeners]);
    }

    /**
     * {@inheritdoc}
     * @return SplPriorityQueue|array
     */
    public function getListenersForEvent(object $event): iterable
    {
        return $this->events[$event::class] ?? [];
    }
}
