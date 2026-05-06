<?php

namespace Protoqol\Quo\Data;

interface QuoPayloadInterface
{
    public function toArray(): array;

    public function toJson(): string;
}
