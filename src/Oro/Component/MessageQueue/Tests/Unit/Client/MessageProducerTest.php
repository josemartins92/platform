<?php
namespace Oro\Component\MessageQueue\Tests\Unit\Client;

use Oro\Component\MessageQueue\Client\Config;
use Oro\Component\MessageQueue\Client\DriverInterface;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\MessageProducer;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\MessageProducerInterface as TransportMessageProducer;
use Oro\Component\MessageQueue\Transport\Null\NullMessage;
use Oro\Component\MessageQueue\Transport\Null\NullQueue;
use Oro\Component\Testing\ClassExtensionTrait;

class MessageProducerTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementMessageProducerInterface()
    {
        $this->assertClassImplements(MessageProducerInterface::class, MessageProducer::class);
    }

    public function testCouldBeConstructedWithRequiredArguments()
    {
        new MessageProducer($this->createTransportMessageProducer(), $this->createDriverStub());
    }

    public function testShouldSendMessageAndCreateSchema()
    {
        $config = new Config('', 'route-message-processor', 'router-queue', '', '');
        $queue = new NullQueue('queue');

        $message = new NullMessage();

        $messageProducer = $this->createTransportMessageProducer();
        $messageProducer
            ->expects($this->once())
            ->method('send')
            ->with($this->identicalTo($queue), $this->identicalTo($message))
        ;

        $driver = $this->createDriverStub($message, $config, $queue);

        $producer = new MessageProducer($messageProducer, $driver);
        $producer->send('topic', 'message');

        $expectedProperties = [
            'oro.message_queue.client.topic_name' => 'topic',
            'oro.message_queue.client.processor_name' => 'route-message-processor',
            'oro.message_queue.client.queue_name' => 'router-queue',
        ];

        $this->assertEquals($expectedProperties, $message->getProperties());
    }

    public function testShouldSendMessageWithNormalPriorityByDefault()
    {
        $config = new Config('', 'route-message-processor', 'router-queue', '', '');
        $queue = new NullQueue('queue');

        $message = new NullMessage();

        $messageProducer = $this->createTransportMessageProducer();

        $driver = $this->createDriverStub($message, $config, $queue);
        $driver
            ->expects($this->once())
            ->method('setMessagePriority')
            ->with($this->identicalTo($message), MessagePriority::NORMAL)
        ;

        $producer = new MessageProducer($messageProducer, $driver);
        $producer->send('topic', 'message');
    }

    public function testShouldSendMessageWithCustomPriority()
    {
        $config = new Config('', 'route-message-processor', 'router-queue', '', '');
        $queue = new NullQueue('queue');

        $message = new NullMessage();

        $messageProducer = $this->createTransportMessageProducer();

        $driver = $this->createDriverStub($message, $config, $queue);
        $driver
            ->expects($this->once())
            ->method('setMessagePriority')
            ->with($this->identicalTo($message), MessagePriority::HIGH)
        ;

        $producer = new MessageProducer($messageProducer, $driver);
        $producer->send('topic', 'message', MessagePriority::HIGH);
    }

    public function testShouldSendNullAsPlainText()
    {
        $config = new Config('', 'route-message-processor', 'router-queue', '', '');
        $queue = new NullQueue('queue');

        $message = new NullMessage();

        $messageProducer = $this->createTransportMessageProducer();
        $messageProducer
            ->expects($this->once())
            ->method('send')
        ;

        $driver = $this->createDriverStub($message, $config, $queue);

        $producer = new MessageProducer($messageProducer, $driver);
        $producer->send('topic', null);

        $this->assertSame('', $message->getBody());
        $this->assertSame('text/plain', $message->getHeader('content_type'));
    }

    public function testShouldSendStringAsPlainText()
    {
        $config = new Config('', 'route-message-processor', 'router-queue', '', '');
        $queue = new NullQueue('queue');

        $message = new NullMessage();

        $messageProducer = $this->createTransportMessageProducer();
        $messageProducer
            ->expects($this->once())
            ->method('send')
        ;

        $driver = $this->createDriverStub($message, $config, $queue);

        $producer = new MessageProducer($messageProducer, $driver);
        $producer->send('topic', 'message');

        $this->assertSame('message', $message->getBody());
        $this->assertSame('text/plain', $message->getHeader('content_type'));
    }

    public function testShouldSendArrayAsJson()
    {
        $config = new Config('', 'route-message-processor', 'router-queue', '', '');
        $queue = new NullQueue('queue');

        $message = new NullMessage();

        $messageProducer = $this->createTransportMessageProducer();
        $messageProducer
            ->expects($this->once())
            ->method('send')
        ;

        $driver = $this->createDriverStub($message, $config, $queue);

        $producer = new MessageProducer($messageProducer, $driver);
        $producer->send('topic', ['foo' => 'fooVal']);

        $this->assertSame('{"foo":"fooVal"}', $message->getBody());
        $this->assertSame('application/json', $message->getHeader('content_type'));
    }

    public function testShouldSendTransportMessage()
    {
        $config = new Config('', 'route-message-processor', 'router-queue', '', '');
        $queue = new NullQueue('queue');

        $message = new NullMessage();

        $messageProducer = $this->createTransportMessageProducer();
        $messageProducer
            ->expects($this->once())
            ->method('send')
            ->with(self::identicalTo($queue), self::identicalTo($message))
        ;

        $driver = $this->createDriverStub(null, $config, $queue);
        $driver
            ->expects(self::never())
            ->method('createMessage')
        ;

        $producer = new MessageProducer($messageProducer, $driver);
        $producer->send('topic', $message);
    }

    public function testThrowIfBodyIsNotSerializable()
    {
        $config = new Config('', 'route-message-processor', 'router-queue', '', '');
        $queue = new NullQueue('queue');

        $message = new NullMessage();

        $messageProducer = $this->createTransportMessageProducer();
        $messageProducer
            ->expects($this->never())
            ->method('send')
        ;

        $driver = $this->createDriverStub($message, $config, $queue);

        $producer = new MessageProducer($messageProducer, $driver);

        $this->setExpectedException(
            \InvalidArgumentException::class,
            'The message\'s body must be either null, scalar or array. Got: stdClass'
        );
        $producer->send('topic', new \stdClass());
    }

    public function testSendShouldForceScalarsToStringAndSetTextContentType()
    {
        $config = new Config('', 'route-message-processor', 'router-queue', '', '');
        $queue = new NullQueue('');
        $message = new NullMessage();

        /** @var MessageInterface $sentMessage */
        $sentMessage = null;

        $messageProcessor = $this->createTransportMessageProducer();
        $messageProcessor
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function ($destination, $message) use (&$sentMessage) {
                $sentMessage = $message;
            })
        ;

        $producer = new MessageProducer($messageProcessor, $this->createDriverStub($message, $config, $queue));
        $producer->send($queue, 12345);

        $this->assertInstanceOf(MessageInterface::class, $sentMessage);

        $headers = $sentMessage->getHeaders();
        self::assertArrayHasKey('content_type', $headers);
        self::assertEquals('text/plain', $headers['content_type']);

        $this->assertInternalType('string', $sentMessage->getBody());
        $this->assertEquals('12345', $sentMessage->getBody());
    }

    public function testSendShouldForceNullToStringAndSetTextContentType()
    {
        $config = new Config('', 'route-message-processor', 'router-queue', '', '');
        $queue = new NullQueue('');
        $message = new NullMessage();

        $sentMessage = null;

        $messageProcessor = $this->createTransportMessageProducer();
        $messageProcessor
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function ($destination, $message) use (&$sentMessage) {
                $sentMessage = $message;
            })
        ;

        $producer = new MessageProducer($messageProcessor, $this->createDriverStub($message, $config, $queue));
        $producer->send($queue, null);

        $this->assertInstanceOf(MessageInterface::class, $sentMessage);

        $headers = $sentMessage->getHeaders();
        self::assertArrayHasKey('content_type', $headers);
        self::assertEquals('text/plain', $headers['content_type']);

        $this->assertInternalType('string', $sentMessage->getBody());
        $this->assertEquals('', $sentMessage->getBody());
    }

    public function testSendShouldSetTimestampHeader()
    {
        $config = new Config('', 'route-message-processor', 'router-queue', '', '');
        $queue = new NullQueue('');
        $message = new NullMessage();

        /** @var MessageInterface $sentMessage */
        $sentMessage = null;

        $messageProcessor = $this->createTransportMessageProducer();
        $messageProcessor
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function ($destination, $message) use (&$sentMessage) {
                $sentMessage = $message;
            })
        ;

        $producer = new MessageProducer($messageProcessor, $this->createDriverStub($message, $config, $queue));
        $producer->send($queue, null);

        self::assertLessThan(time() + 5, $sentMessage->getTimestamp());
        self::assertGreaterThan(time() - 5, $sentMessage->getTimestamp());
    }

    public function testSendShouldSetMessageIdHeader()
    {
        $config = new Config('', 'route-message-processor', 'router-queue', '', '');
        $queue = new NullQueue('');
        $message = new NullMessage();

        /** @var MessageInterface $sentMessage */
        $sentMessage = null;

        $messageProcessor = $this->createTransportMessageProducer();
        $messageProcessor
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function ($destination, $message) use (&$sentMessage) {
                $sentMessage = $message;
            })
        ;

        $producer = new MessageProducer($messageProcessor, $this->createDriverStub($message, $config, $queue));
        $producer->send($queue, null);

        self::assertInternalType('string', $sentMessage->getMessageId());
        self::assertNotEmpty($sentMessage->getMessageId());
    }

    public function testSendShouldThrowExceptionIfBodyIsObject()
    {
        $config = new Config('', 'route-message-processor', 'router-queue', '', '');
        $queue = new NullQueue('');
        $message = new NullMessage();

        $messageProcessor = $this->createTransportMessageProducer();

        $producer = new MessageProducer($messageProcessor, $this->createDriverStub($message, $config, $queue));

        $this->setExpectedException(
            \LogicException::class,
            'The message\'s body must be either null, scalar or array. Got: stdClass'
        );
        $producer->send($queue, new \stdClass);
    }

    public function testShouldCreateMessageForNullBody()
    {
        $expectedMessage = new NullMessage();

        $producer = new MessageProducer(
            $this->createTransportMessageProducer(),
            $this->createDriverStub($expectedMessage, null, null)
        );

        $actualMessage = $producer->createMessage(null);

        self::assertSame($expectedMessage, $actualMessage);
        self::assertNotEmpty($actualMessage->getMessageId());
        self::assertNotEmpty($actualMessage->getTimestamp());
        self::assertEquals('text/plain', $actualMessage->getHeader('content_type'));
        self::assertEquals('', $actualMessage->getBody());
    }

    public function testShouldCreateMessageForStringBody()
    {
        $expectedMessage = new NullMessage();

        $producer = new MessageProducer(
            $this->createTransportMessageProducer(),
            $this->createDriverStub($expectedMessage, null, null)
        );

        $actualMessage = $producer->createMessage('aString');

        self::assertSame($expectedMessage, $actualMessage);
        self::assertNotEmpty($actualMessage->getMessageId());
        self::assertNotEmpty($actualMessage->getTimestamp());
        self::assertEquals('text/plain', $actualMessage->getHeader('content_type'));
        self::assertEquals('aString', $actualMessage->getBody());
    }

    public function testShouldCreateMessageForArray()
    {
        $expectedMessage = new NullMessage();

        $producer = new MessageProducer(
            $this->createTransportMessageProducer(),
            $this->createDriverStub($expectedMessage, null, null)
        );

        $actualMessage = $producer->createMessage(['foo' => 'fooVal']);

        self::assertSame($expectedMessage, $actualMessage);
        self::assertNotEmpty($actualMessage->getMessageId());
        self::assertNotEmpty($actualMessage->getTimestamp());
        self::assertEquals('application/json', $actualMessage->getHeader('content_type'));
        self::assertEquals('{"foo":"fooVal"}', $actualMessage->getBody());
    }

    public function testShouldThrowExceptionIfBodyIsObjectOnCreateMessage()
    {
        $expectedMessage = new NullMessage();

        $producer = new MessageProducer(
            $this->createTransportMessageProducer(),
            $this->createDriverStub($expectedMessage, null, null)
        );

        $this->setExpectedException(
            \LogicException::class,
            'The message\'s body must be either null, scalar or array. Got: stdClass'
        );
        $producer->createMessage(new \stdClass());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|DriverInterface
     */
    protected function createDriverStub($message = null, $config = null, $queue = null)
    {
        $driverMock = $this->getMock(DriverInterface::class, [], [], '', false);
        $driverMock
            ->expects($this->any())
            ->method('createMessage')
            ->will($this->returnValue($message))
        ;
        $driverMock
            ->expects($this->any())
            ->method('getConfig')
            ->will($this->returnValue($config))
        ;
        $driverMock
            ->expects($this->any())
            ->method('createQueue')
            ->will($this->returnValue($queue))
        ;

        return $driverMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|TransportMessageProducer
     */
    protected function createTransportMessageProducer()
    {
        return $this->getMock(TransportMessageProducer::class, [], [], '', false);
    }
}
