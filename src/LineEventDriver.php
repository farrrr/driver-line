<?php

namespace FarLab\Drivers\Line;

use BotMan\BotMan\Interfaces\DriverEventInterface;
use FarLab\Drivers\Line\Events\LineEvent;
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
     * @return bool|DriverEventInterface
     */
    public function hasMatchingEvent()
    {
        if ($this->event && $this->event->count()) {
            $this->driverEvent = $this->getEventFromEventData($this->event->first());
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