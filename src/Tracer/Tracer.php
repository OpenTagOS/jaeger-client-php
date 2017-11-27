<?php
declare(strict_types=1);

namespace Jaeger\Tracer;

use Jaeger\Client\ClientInterface;
use Jaeger\Span\Context\ContextAwareInterface;
use Jaeger\Span\Context\SpanContext;
use Jaeger\Span\Factory\SpanFactoryInterface;
use Jaeger\Span\SpanInterface;
use Ds\Stack;

class Tracer implements TracerInterface, ContextAwareInterface, InjectableInterface, FlushableInterface
{
    private $stack;

    private $factory;

    private $client;

    public function __construct(Stack $stack, SpanFactoryInterface $factory, ClientInterface $client)
    {
        $this->stack = $stack;
        $this->factory = $factory;
        $this->client = $client;
    }

    public function flush(): FlushableInterface
    {
        $this->client->flush();
        if (0 !== $this->stack->count()) {
            throw new \RuntimeException('Corrupted stack');
        }

        return $this;
    }

    public function assign(SpanContext $context): InjectableInterface
    {
        $this->stack->push([$context]);

        return $this;
    }

    public function getContext(): ?SpanContext
    {
        if (0 === $this->stack->count()) {
            return null;
        }

        return $this->stack->peek();
    }

    public function start(string $operationName, array $tags = []): SpanInterface
    {
        $span = $this->factory->create($operationName, $tags, $this->getContext());
        $this->stack->push($span->getContext());

        return $span;
    }

    public function finish(SpanInterface $span): TracerInterface
    {
        $this->client->add($span->finish());
        $this->stack->pop();

        return $this;
    }
}
