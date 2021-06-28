<?php

namespace Spatie\DataTransferObject\Casters;

use ArrayAccess;
use LogicException;
use Spatie\DataTransferObject\Caster;
use Traversable;

class ArrayCaster implements Caster
{
    public function __construct(
        private string $type,
        private string $itemType,
    ) {
    }

    public function cast(mixed $value): array|ArrayAccess
    {
        if ($this->type == 'array') {
            return $this->castArray($value);
        }

        if (is_subclass_of($this->type, ArrayAccess::class)) {
            return $this->castArrayAccess($value);
        }

        throw new LogicException("Caster [ArrayCaster] may only be used to cast arrays or objects that implement ArrayAccess.");
    }

    private function castArray(mixed $value): array
    {
        return array_map(
            fn (array $data) => new $this->itemType(...$data),
            $value
        );
    }

    private function castArrayAccess(mixed $value): ArrayAccess
    {
        if (! is_subclass_of($this->type, Traversable::class)) {
            throw new LogicException("Caster [ArrayCaster] may only be used to cast ArrayAccess objects that are traversable.");
        }

        $arrayAccess = new $this->type();

        foreach ($value as $key => $data) {
            $arrayAccess[$key] = new $this->itemType(...(array) $data);
        }

        return $arrayAccess;
    }
}
