<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

use libMarshal\parser\ArrayParseable;

/**
 * @template K of array-key
 * @template V of mixed
 * @implements ArrayParseable<array<K, ArgConfig>, K, V>
 */
class ArgConfigParser implements ArrayParseable
{
    public function parse(mixed $value) : mixed
    {
        $args = [];
        foreach ($value as $k => $v) {
            /**
             * @var scalar[] $v
             */
            $args[$k] = ArgConfig::unmarshal($v);
        }

        return $args;
    }

    public function serialize(mixed $value) : array
    {
        $data = [];
        foreach ($value as $k => $v) {
            $data[$k] = $v->marshal();
        }

        /**
         * @var array<K, V>
         */
        return $data;
    }
}