<?php

namespace FarLab\Drivers\Line\Attachments;

use BotMan\BotMan\Messages\Attachments\Attachment;

abstract class Downloadable extends Attachment
{
    protected $messageId;

    /**
     * Downloadable constructor.
     *
     * @param       $messageId
     * @param mixed $payload
     */
    public function __construct($messageId, $payload = null)
    {
        parent::__construct($payload);

        $this->messageId = $messageId;
    }

    public static function create(...$args)
    {
        return new static(...$args);
    }

    public function getMessageId()
    {
        return $this->messageId;
    }

    abstract protected function getAttachmentType();

    public function toWebDriver()
    {
        return [
            'type' => $this->getAttachmentType(),
            'messageId' => $this->messageId,
        ];
    }
}