<?php

namespace FarLab\Drivers\Line\Events;

use BotMan\BotMan\Interfaces\DriverEventInterface;
use LINE\LINEBot\Event\BaseEvent;

class LineEvent implements DriverEventInterface
{
    /** @var BaseEvent */
    protected $payload;

    /**
     * @param $payload
     */
    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    /**
     * Return the event name to match.
     *
     * @return string
     */
    public function getName()
    {
        return $this->payload->getType();
    }

    /**
     * Return the event payload.
     *
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }
}