<?php

namespace FarLab\Drivers\Line;

use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use FarLab\Drivers\Line\Attachments\File;
use LINE\LINEBot\Event\BaseEvent;
use LINE\LINEBot\Event\MessageEvent\FileMessage;

class LineFileDriver extends LineDriver
{
    const DRIVER_NAME = 'LineFile';

    protected function eventFilter($event)
    {
        return $event instanceof FileMessage;
    }

    /**
     * @param BaseEvent $event
     * @return IncomingMessage
     */
    protected function transformMessage(BaseEvent $event)
    {
        /** @var FileMessage $event */
        $message = new IncomingMessage(File::PATTERN, $this->getMessageSender($event), $this->getMessageRecipient($event), $event);
        $file = new File($event->getMessageId());
        $file->addExtras('filename', $event->getFileName());
        $file->addExtras('filesize', $event->getFileSize());

        $message->setFiles([$file]);

        return $message;
    }

    public function isConfigured()
    {
        return false;
    }

}