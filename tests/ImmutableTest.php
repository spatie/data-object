<?php

namespace Spatie\DataTransferObject\Tests;

use Spatie\DataTransferObject\DataTransferObjectError;
use Spatie\DataTransferObject\TestClass;
use Spatie\DataTransferObject\Tests\TestClasses\ImmutablePropertyDataTransferObject;
use Spatie\DataTransferObject\Tests\TestClasses\TestDataTransferObject;

class ImmutableTest extends TestCase
{
    /** @test */
    public function immutable_values_cannot_be_overwritten()
    {
        $dto = TestDataTransferObject::immutable([
            'testProperty' => 1,
        ]);

        $this->assertEquals(1, $dto->testProperty);

        $this->expectException(DataTransferObjectError::class);

        $dto->testProperty = 2;
    }

    /** @test */
    public function mutable_values_can_be_overwritten()
    {
        $dto = TestDataTransferObject::mutable([
            'testProperty' => 1,
        ]);

        $this->assertEquals(1, $dto->testProperty);

        $dto->testProperty = 2;

        $this->assertEquals(2, $dto->testProperty);
    }

    /** @test */
    public function method_calls_are_proxied()
    {
        $dto = TestDataTransferObject::immutable([
            'testProperty' => 1,
        ]);

        $this->assertEquals(['testProperty' => 1], $dto->toArray());
    }

    /** @test */
    public function immutable_is_default()
    {
        $dto = new TestDataTransferObject([
            'testProperty' => 1,
        ]);

        $this->assertEquals(1, $dto->testProperty);

        $this->expectException(DataTransferObjectError::class);

        $dto->testProperty = 2;
    }

    /** @test */
    public function property_is_immutable()
    {
        $dto = ImmutablePropertyDataTransferObject::mutable([
            'testProperty' => "astring",
        ]);

        $this->assertEquals("astring", $dto->testProperty);

        $this->expectException(DataTransferObjectError::class);

        $dto->testProperty = 'otherstring';
    }
}
