<?php

namespace FarLab\Drivers\Line;

use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use FarLab\Drivers\Line\Attachments\Audio;
use LINE\LINEBot\Event\BaseEvent;
use LINE\LINEBot\Event\MessageEvent\AudioMessage;

class LineAudioDriver extends LineDriver
{
    const DRIVER_NAME = 'LineAudio';

    /**
     * @param $event
     * @return bool
     */
    protected function eventFilter($event)
    {
        return $event instanceof AudioMessage;
    }

    /**
     * @param BaseEvent $event
     * @return IncomingMessage
     */
    protected function transformMessage(BaseEvent $event)
    {
        /** @var AudioMessage $event */
        $message = new IncomingMessage(Audio::PATTERN, $this->getMessageSender($event), $this->getMessageRecipient($event), $event);
        $message->setAudio([new Audio($event->getMessageId())]);

        return $message;
    }

    public function isConfigured()
    {
        return false;
    }
}