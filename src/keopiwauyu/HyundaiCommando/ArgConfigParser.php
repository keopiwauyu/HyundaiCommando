<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

use libMarshal\exception\GeneralMarshalException;
use libMarshal\exception\UnmarshalException;
use libMarshal\parser\ArrayParseable;

/**
 * @template K of array-key
 * @template V of mixed
 * @implements ArrayParseable<array<K, ArgConfig|string>, K, V>
 */
class ArgConfigParser implements ArrayParseable
{
    public function parse(mixed $value) : mixed
    {
        $args = [];
        foreach ($value as $k => $v) {
            $args[$k] = !is_array($v) ? (string)$v : ArgConfig::unmarshal($v);
        }

        return $args;
    }

    public function serialize(mixed $value) : array
    {
        $data = [];
        foreach ($value as $k => $v) {
$data[$k] =$v instanceof ArgConfig ? $v->marshal() : $v;
        }

        /**
         * @var array<K, V>
         */
        return $data;
    }
}