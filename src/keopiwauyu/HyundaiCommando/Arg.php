<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\args\BaseArgument;
use SOFe\AwaitGenerator\Loading;
use SOFe\AwaitGenerator\Mutex;
use keopiwauyu\HyundaiCommando\ArgConfig;
use libMarshal\MarshalTrait;
use libMarshal\attributes\Field;
use libMarshal\exception\GeneralMarshalException;
use libMarshal\exception\UnmarshalException;

class Arg
{
    use MarshalTrait;

    public string $id;

    /**
     * @var Loading<BaseArgument>
     */
    public Loading $loading;

    public bool $used = false;

    /**
     * @param mixed[] $other
     */
    public function __construct(
        #[Field] public string $name = "", // TODO: support langusges??
        #[Field] public string $type = "",
        #[Field] public bool $optional = false,
        #[Field] public array $other = []
    ) {
    }

    public self $config;

    /**
     * @param mixed[] $data
     * @param array<string, self> $args
     * @throws \Exception
     * @throws GeneralMarshalException
     * @throws UnmarshalException
     */
    public static function unmarshalAndLoad(array $data, array &$args, Mutex $lock) : self {
        $self = self::unmarshal($data);
        $self->config = $self;
        $self->loading = new Loading(function () use (&$args, $lock, $self) : \Generator {
        yield from $lock->acquire();
        $lock->release();

        /**
         * @var WantFactoryEvent<self, BaseArgument> $event
         */
        $event = new WantFactoryEvent($self, $args);
        $event->call();
        return yield from $event->getFactory();
        });

        return $self;
    }

    public function getType() : string {
        return strtolower($this->type);
    }
}