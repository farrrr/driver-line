<?php

namespace FarLab\Drivers\Line\Attachments;

use BotMan\BotMan\Messages\Attachments\Video as BotManVideo;

class Video extends Downloadable
{
    const PATTERN = BotManVideo::PATTERN;

    protected function getAttachmentType()
    {
        return 'image';
    }
}