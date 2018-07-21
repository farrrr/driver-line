<?php

namespace FarLab\Drivers\Line\Attachments;

use BotMan\BotMan\Messages\Attachments\Audio as BotManAudio;

class Audio extends Downloadable
{
    const PATTERN = BotManAudio::PATTERN;

    protected function getAttachmentType()
    {
        return 'audio';
    }
}