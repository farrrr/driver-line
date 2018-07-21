<?php

namespace FarLab\Drivers\Line;

use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use FarLab\Drivers\Line\Attachments\Video;
use LINE\LINEBot\Event\BaseEvent;
use LINE\LINEBot\Event\MessageEvent\VideoMessage;

class LineVideoDriver extends LineDriver
{
    const DRIVER_NAME = 'LineVideo';

    /**
     * @param $event
     * @return bool
     */
    protected function eventFilter($event)
    {
        return $event instanceof VideoMessage;
    }

    /**
     * @param BaseEvent $event
     * @return IncomingMessage
     */
    protected function transformMessage(BaseEvent $event)
    {
        /** @var VideoMessage $event */
        $message = new IncomingMessage(Video::PATTERN, $this->getMessageSender($event), $this->getMessageRecipient($event), $event);
        $message->setVideos([new Video($event->getMessageId())]);

        return $message;
    }

    public function isConfigured()
    {
        return false;
    }
}