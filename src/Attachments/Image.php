<?php

namespace FarLab\Drivers\Line\Attachments;

use BotMan\BotMan\Messages\Attachments\Image as BotManImage;

class Image extends Downloadable
{
    const PATTERN = BotManImage::PATTERN;

    protected function getAttachmentType()
    {
        return 'image';
    }
}