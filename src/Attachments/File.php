<?php

namespace FarLab\Drivers\Line\Attachments;

use BotMan\BotMan\Messages\Attachments\File as BotManFile;

class File extends Downloadable
{
    const PATTERN = BotManFile::PATTERN;

    protected function getAttachmentType()
    {
        return 'file';
    }
}