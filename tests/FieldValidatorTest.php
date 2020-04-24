<?php

declare(strict_types=1);

namespace Spatie\DataTransferObject\Tests;

use Spatie\DataTransferObject\FieldType;
use Spatie\DataTransferObject\FieldValidator;

class Foo {}
class FooChild extends Foo {}
class Bar {}

class FieldValidatorTest extends TestCase
{
    /** @test */
    public function nullable()
    {
        $this->assertTrue((new FieldValidator())->isNullable);
        $this->assertTrue((new FieldValidator('/**  */'))->isNullable);
        $this->assertTrue((new FieldValidator('/** @var string|null */'))->isNullable);
        $this->assertTrue((new FieldValidator('/** @var null */'))->isNullable);
        $this->assertTrue((new FieldValidator('/** @var mixed */'))->isNullable);

        $this->assertFalse((new FieldValidator('/** @var string */'))->isNullable);
    }

    /** @test */
    public function allowed_types()
    {
        $this->assertEquals([new FieldType('string')], (new FieldValidator('/** @var string */'))->allowedTypes);
        $this->assertEquals([new FieldType('\A\B')], (new FieldValidator('/** @var \A\B */'))->allowedTypes);
        $this->assertEquals([new FieldType('string'), new FieldType('integer')], (new FieldValidator('/** @var string|integer */'))->allowedTypes);
        $this->assertEquals([new FieldType('string'), new FieldType('integer')], (new FieldValidator('/** @var string|int */'))->allowedTypes);
        $this->assertEquals([new FieldType('boolean')], (new FieldValidator('/** @var bool */'))->allowedTypes);
        $this->assertEquals([new FieldType('double')], (new FieldValidator('/** @var float */'))->allowedTypes);
    }

    /** @test */
    public function allowed_array_types()
    {
        $this->assertEquals([new FieldType('string')], (new FieldValidator('/** @var string[] */'))->allowedArrayTypes);
        $this->assertEquals([new FieldType('\A\B')], (new FieldValidator('/** @var \A\B[] */'))->allowedArrayTypes);
        $this->assertEquals([new FieldType('string'), new FieldType('integer')], (new FieldValidator('/** @var string[]|int[] */'))->allowedArrayTypes);
        $this->assertEquals([new FieldType('string')], (new FieldValidator('/** @var string[]|int */'))->allowedArrayTypes);
        $this->assertEquals([new FieldType('string')], (new FieldValidator('/** @var iterable<string> */'))->allowedArrayTypes);
        $this->assertEquals([new FieldType('string'), new FieldType('integer')], (new FieldValidator('/** @var iterable<string>|int[] */'))->allowedArrayTypes);
        $this->assertEquals([new FieldType('string')], (new FieldValidator('/** @var array<string> */'))->allowedArrayTypes);
        $this->assertEquals([new FieldType('integer')], (new FieldValidator('/** @var array<int> */'))->allowedArrayTypes);
        $this->assertEquals([new FieldType('string', 'integer')], (new FieldValidator('/** @var array<int,string> */'))->allowedArrayTypes);
        $this->assertEquals([new FieldType('integer', 'string')], (new FieldValidator('/** @var array<string,int> */'))->allowedArrayTypes);
        $this->assertEquals([new FieldType('\A\B', 'string')], (new FieldValidator('/** @var array<string,\A\B> */'))->allowedArrayTypes);
    }

    /** @test */
    public function empty_type_is_always_valid()
    {
        $this->assertTrue((new FieldValidator())->isValidType(1));
        $this->assertTrue((new FieldValidator())->isValidType('a'));
        $this->assertTrue((new FieldValidator())->isValidType(null));
    }

    /** @test */
    public function mixed_is_always_valid()
    {
        $this->assertTrue((new FieldValidator('/** @var mixed */'))->isValidType(1));
        $this->assertTrue((new FieldValidator('/** @var mixed */'))->isValidType('a'));
        $this->assertTrue((new FieldValidator('/** @var mixed */'))->isValidType(null));
    }

    /** @test */
    public function nullable_types_are_validated()
    {
        $this->assertTrue((new FieldValidator())->isValidType(null));
        $this->assertTrue((new FieldValidator('/**  */'))->isValidType(null));
        $this->assertTrue((new FieldValidator('/** @var string|null */'))->isValidType(null));
        $this->assertTrue((new FieldValidator('/** @var null */'))->isValidType(null));
        $this->assertTrue((new FieldValidator('/** @var mixed */'))->isValidType(null));
        $this->assertTrue((new FieldValidator('/** @var ?string */'))->isValidType(null));
    }

    /** @test */
    public function arrays_types_are_validated()
    {
        $this->assertTrue((new FieldValidator('/** @var string[] */'))->isValidType(['a']));
        $this->assertTrue((new FieldValidator('/** @var iterable<string> */'))->isValidType(['a']));

        $this->assertFalse((new FieldValidator('/** @var string[] */'))->isValidType([1]));
        $this->assertFalse((new FieldValidator('/** @var string[] */'))->isValidType('a'));
    }

    /** @test */
    public function any_type_of_array_or_iterable_is_allowed()
    {
        $this->assertTrue((new FieldValidator('/** @var array */'))->isValidType(['a', 1]));
        $this->assertTrue((new FieldValidator('/** @var iterable */'))->isValidType(['a', 1]));

        $this->assertFalse((new FieldValidator('/** @var string[] */'))->isValidType(['a', 1]));
        $this->assertFalse((new FieldValidator('/** @var iterable<string> */'))->isValidType(['a', 1]));
        $this->assertFalse((new FieldValidator('/** @var string[] */'))->isValidType(['a', 1]));
    }

    /** @test */
    public function types_are_validated()
    {
        $this->assertTrue((new FieldValidator('/** @var string */'))->isValidType('a'));
        $this->assertTrue((new FieldValidator('/** @var float */'))->isValidType(1.0));
        $this->assertTrue((new FieldValidator('/** @var int */'))->isValidType(1));
        $this->assertTrue((new FieldValidator('/** @var int|float */'))->isValidType(1));
        $this->assertTrue((new FieldValidator('/** @var int|float */'))->isValidType(1.0));
        $this->assertTrue((new FieldValidator('/** @var int|string */'))->isValidType(1));
        $this->assertTrue((new FieldValidator('/** @var int|string */'))->isValidType('a'));
        $this->assertTrue((new FieldValidator('/** @var string|null */'))->isValidType('a'));
        $this->assertTrue((new FieldValidator('/** @var \Spatie\DataTransferObject\Tests\Foo */'))->isValidType(new Foo));
        $this->assertTrue((new FieldValidator('/** @var \Spatie\DataTransferObject\Tests\Foo */'))->isValidType(new FooChild));

        $this->assertFalse((new FieldValidator('/** @var string */'))->isValidType(1));
        $this->assertFalse((new FieldValidator('/** @var \Spatie\DataTransferObject\Tests\Foo */'))->isValidType(new Bar));
    }
}
