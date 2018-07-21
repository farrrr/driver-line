<?php

namespace FarLab\Drivers\Line;

use BotMan\BotMan\Messages\Attachments\Location;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use LINE\LINEBot\Event\BaseEvent;
use LINE\LINEBot\Event\MessageEvent\LocationMessage;

class LineLocationDriver extends LineDriver
{
    const DRIVER_NAME = 'LineLocation';

    protected function eventFilter($event)
    {
        return $event instanceof LocationMessage;
    }

    /**
     * @param BaseEvent $event
     * @return IncomingMessage
     */
    protected function transformMessage(BaseEvent $event)
    {
        /** @var LocationMessage $event */
        $message = new IncomingMessage(Location::PATTERN, $this->getMessageSender($event), $this->getMessageRecipient($event), $event);
        $location = new Location($event->getLatitude(), $event->getLongitude());
        $location->addExtras('title', $event->getTitle());
        $location->addExtras('address', $event->getAddress());
        $message->setLocation($location);

        return $message;
    }

    public function isConfigured()
    {
        return false;
    }
}