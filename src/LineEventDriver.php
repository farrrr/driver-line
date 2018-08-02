<?php

namespace FarLab\Drivers\Line;

use BotMan\BotMan\Interfaces\DriverEventInterface;
use FarLab\Drivers\Line\Events\LineEvent;
use Illuminate\Support\Collection;
use LINE\LINEBot\Event\BaseEvent;
use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\Event\PostbackEvent;

class LineEventDriver extends LineDriver
{
    const DRIVER_NAME = 'LineEvent';

    /** @var DriverEventInterface */
    protected $driverEvent;

    protected function eventFilter($event)
    {
        return $event instanceof BaseEvent
            && ! $event instanceof PostbackEvent
            && ! $event instanceof MessageEvent;
    }

    /**
     * @return bool|DriverEventInterface|Collection
     */
    public function hasMatchingEvent()
    {
        if ($this->event && $this->event->count()) {
            $this->driverEvent = Collection::make($this->event)->map(function ($event) {
                return $this->getEventFromEventData($event);
            });
        }

        return $this->driverEvent ?: false;
    }

    /**
     * @param $event
     * @return LineEvent
     */
    protected function getEventFromEventData($event)
    {
        return new LineEvent($event);
    }

    /**
     * @return bool
     */
    public function isConfigured()
    {
        return false;
    }

}