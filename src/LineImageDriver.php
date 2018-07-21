<?php

namespace FarLab\Drivers\Line;

use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use FarLab\Drivers\Line\Attachments\Image;
use LINE\LINEBot\Event\BaseEvent;
use LINE\LINEBot\Event\MessageEvent\ImageMessage;

class LineImageDriver extends LineDriver
{
    const DRIVER_NAME = 'LineImage';

    protected function eventFilter($event)
    {
        return $event instanceof ImageMessage;
    }

    /**
     * @param BaseEvent $event
     * @return IncomingMessage
     */
    protected function transformMessage(BaseEvent $event)
    {
        /** @var ImageMessage $event */
        $message = new IncomingMessage(Image::PATTERN, $this->getMessageSender($event), $this->getMessageRecipient($event), $event);
        $message->setImages([new Image($event->getMessageId())]);

        return $message;
    }

    public function isConfigured()
    {
        return false;
    }
}