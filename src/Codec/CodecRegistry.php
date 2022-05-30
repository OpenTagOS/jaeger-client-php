<?php
declare(strict_types=1);

namespace Jaeger\Codec;

class CodecRegistry implements \ArrayAccess
{
    private $codecs = [];

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->codecs);
    }

    public function offsetGet($offset): mixed
    {
        if (false === array_key_exists($offset, $this->codecs)) {
            return null;
        }

        return $this->codecs[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        $this->codecs[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        if (false === array_key_exists($offset, $this->codecs)) {
            return;
        }
        unset($this->codecs[$offset]);
    }
}
