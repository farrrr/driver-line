<?php

namespace FarLab\Drivers\Line;

use BotMan\BotMan\Drivers\HttpDriver;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Users\User;
use FarLab\Drivers\Line\Exceptions\LineException;
use FarLab\Drivers\Line\Exceptions\UnsupportedAttachmentException;
use Illuminate\Support\Collection;
use LINE\LINEBot;
use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\Event\BaseEvent;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\Response;
use Symfony\Component\HttpFoundation\Request;

class LineDriver extends HttpDriver
{
    const DRIVER_NAME = 'Line';

    /** @var LINEBot */
    protected $api;
    /** @var string */
    protected $signature;
    /** @var array */
    protected $messages = [];


    /**
     * @param Request $request
     */
    public function buildPayload(Request $request)
    {
        $this->content = $request->getContent();
        $this->config = Collection::make($this->config->get('line', []));
        $this->signature = $request->headers->get(HTTPHeader::LINE_SIGNATURE);
        $this->api = new LINEBot(
            new CurlHTTPClient($this->config->get('access_token')),
            [ 'channelSecret' => $this->config->get('channel_secret') ]
        );
    }

    /**
     * @return bool
     */
    public function matchesRequest()
    {
        try {
            $events = Collection::make($this->api->parseEventRequest($this->content, $this->signature));
        } catch (\Exception $e) {
            return false;
        }

        $this->event = $events->filter(function ($event) {
            return $this->eventFilter($event);
        });

        return ! $this->event->isEmpty();
    }

    /**
     * @param $event
     * @return bool
     */
    protected function eventFilter($event)
    {
        return $event instanceof TextMessage;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        if (empty($this->messages)) {
            $this->loadMessages();
        }

        return $this->messages;
    }

    /**
     * Load messages
     */
    protected function loadMessages()
    {
        $messages = $this->event->map(function ($event) {
            return $this->transformMessage($event);
        })->toArray();

        $this->messages = $messages;
    }

    /**
     * @param BaseEvent $event
     * @return IncomingMessage
     */
    protected function transformMessage(BaseEvent $event)
    {
        $message = new IncomingMessage('', $this->getMessageSender($event), $this->getMessageRecipient($event), $event);

        if ($event instanceof TextMessage) {
            $message->setText($event->getText());
        }

        return $message;
    }

    /**
     * @param BaseEvent $event
     * @return null|string
     */
    protected function getMessageSender(BaseEvent $event)
    {
        return $event->getUserId();
    }

    /**
     * @param BaseEvent $event
     * @return null|string
     */
    protected function getMessageRecipient(BaseEvent $event)
    {
        return $event->getEventSourceId();
    }

    /**
     * @param IncomingMessage $matchingMessage
     * @return \BotMan\BotMan\Interfaces\UserInterface
     * @throws LINEBot\Exception\InvalidEventSourceException
     * @throws LineException
     */
    public function getUser(IncomingMessage $matchingMessage)
    {
        /** @var BaseEvent $event */
        $event = $matchingMessage->getPayload();
        $sender = $matchingMessage->getSender();

        if ($event->isGroupEvent()) {
            $response = $this->api->getGroupMemberProfile($event->getGroupId(), $sender);
        } elseif ($event->isRoomEvent()) {
            $response = $this->api->getRoomMemberProfile($event->getRoomId(), $sender);
        } else {
            $response = $this->api->getProfile($sender);
        }

        $this->throwExceptionIfResponseNotOk($response);

        $profile = $response->getJSONDecodedBody();

        return new User($sender, null, null, $profile['displayName'], $profile);
    }

    /**
     * @param IncomingMessage $message
     * @return Answer
     */
    public function getConversationAnswer(IncomingMessage $message)
    {
        return Answer::create($message->getText())->setMessage($message);
    }

    /**
     * @param string|Question|OutgoingMessage|MessageBuilder|array $message
     * @param IncomingMessage $matchingMessage
     * @param array           $additionalParameters
     * @return array
     * @throws LineException
     * @throws UnsupportedAttachmentException
     */
    public function buildServicePayload($message, $matchingMessage, $additionalParameters = [])
    {
        $event = $matchingMessage->getPayload();
        $parameters = [];

        if ($event instanceof BaseEvent && $replyToken = $event->getReplyToken()) {
            $parameters['replyToken'] = $replyToken;
        } else {
            $parameters['to'] = $matchingMessage->getRecipient();
        }

        if (is_array($message)) {
            $builder = new MultiMessageBuilder();

            foreach ($message as $msg) {
                $builder->add($this->buildMessage($msg));
            }

            $parameters['message'] = $builder;
        } else {
            $parameters['message'] = $this->buildMessage($message);
        }

        return $parameters;
    }

    /**
     * @param string|Question|OutgoingMessage|MessageBuilder $message
     * @return MessageBuilder
     * @throws LineException
     * @throws UnsupportedAttachmentException
     */
    protected function buildMessage($message)
    {
        if (is_string($message)) {
            return new TextMessageBuilder($message);
        } elseif ($message instanceof Question) {
            // TODO: implement whole Question Object.
            return new TextMessageBuilder($message->getText());
        } elseif ($message instanceof OutgoingMessage) {
            if ($message->getAttachment() !== null) {
                throw new UnsupportedAttachmentException('OutgoingMessage Attachment is not supported. Please use line-bot-sdk MessageBuilder instead.');
            }

            return new TextMessageBuilder($message->getText());
        } elseif ($message instanceof MessageBuilder) {
            return $message;
        }

        throw new LineException('Message type is not supported.');
    }

    /**
     * @param mixed $payload
     * @return Response|\Symfony\Component\HttpFoundation\Response
     * @throws LineException
     */
    public function sendPayload($payload)
    {
        $payload = Collection::make($payload);

        if ($replyToken = $payload->get('replyToken')) {
            $response = $this->api->replyMessage($replyToken, $payload->get('message'));
        } else {
            $response = $this->api->pushMessage($payload->get('to'), $payload->get('message'));
        }

        $this->throwExceptionIfResponseNotOk($response);

        return $response;
    }

    /**
     * @return bool
     */
    public function isBot()
    {
        return false;
    }

    /**
     * @param Response $response
     * @throws LineException
     */
    protected function throwExceptionIfResponseNotOk(Response $response)
    {
        if (! $response->isSucceeded()) {
            throw new LineException('Error sending payload: ' . $response->getJSONDecodedBody()['message']);
        }
    }

    /**
     * @return bool
     */
    public function isConfigured()
    {
        return ! is_null($this->config->get('channel_secret'))
            && ! is_null($this->config->get('access_token'));
    }

    /**
     * Low-level method to perform driver specific API requests.
     *
     * @param string                                           $endpoint
     * @param array                                            $parameters
     * @param \BotMan\BotMan\Messages\Incoming\IncomingMessage $matchingMessage
     * @return void
     */
    public function sendRequest($endpoint, array $parameters, IncomingMessage $matchingMessage)
    {
        // TODO: Implement sendRequest() method.
    }
}