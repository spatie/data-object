<?php

declare(strict_types=1);

namespace Spatie\DataTransferObject\Tests\TestClasses;

use Spatie\DataTransferObject\DataTransferObject;

class TypedNestedParent extends DataTransferObject
{
    public NestedChild $child;

    /** @var string */
    public $name;
}
