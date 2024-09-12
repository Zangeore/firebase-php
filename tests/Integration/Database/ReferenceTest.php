<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration\Database;

use Iterator;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Database\Reference;
use Kreait\Firebase\Tests\Integration\DatabaseTestCase;

/**
 * @internal
 */
final class ReferenceTest extends DatabaseTestCase
{
    private Reference $ref;
    protected function setUp(): void
    {
        $this->ref = self::$db->getReference(self::$refPrefix);
    }
    /**
     * @param mixed $value
     */
    public function setAndGet(string $key, $value): void
    {
        $ref = $this->ref->getChild(__FUNCTION__.'/'.$key);
        $ref->set($value);
        $this->assertSame($value, $ref->getValue());
    }
    public function update(): void
    {
        $ref = $this->ref->getChild(__FUNCTION__);
        $ref->set([
            'first' => 'value',
            'second' => 'value',
        ]);
        $ref->update([
            'first' => 'updated',
            'third' => 'new',
        ]);
        $expected = [
            'first' => 'updated',
            'second' => 'value',
            'third' => 'new',
        ];
        $this->assertEqualsCanonicalizing($expected, $ref->getValue());
    }
    public function push(): void
    {
        $ref = $this->ref->getChild(__FUNCTION__);
        $value = 'a value';
        $newRef = $ref->push($value);
        $this->assertSame(1, $ref->getSnapshot()->numChildren());
        $this->assertSame($value, $newRef->getValue());
    }
    public function remove(): void
    {
        $ref = $this->ref->getChild(__FUNCTION__);
        $ref->set([
            'first' => 'value',
            'second' => 'value',
        ]);
        $ref->getChild('first')->remove();
        $this->assertEqualsCanonicalizing(['second' => 'value'], $ref->getValue());
    }
    public function removeChildren(): void
    {
        $ref = $this->ref->getChild(__FUNCTION__);
        $ref->set([
            'first' => 'value',
            'second' => [
                'first_nested' => 'value',
                'second_nested' => 'value',
            ],
            'third' => 'value',
        ]);
        $ref->removeChildren([
            'first',
            'second/first_nested',
        ]);
        $this->assertEqualsCanonicalizing([
            'second' => [
                'second_nested' => 'value',
            ],
            'third' => 'value',
        ], $ref->getValue());
    }
    public function pushToGetKey(): void
    {
        $ref = $this->ref->getChild(__FUNCTION__);
        $key = $ref->push()->getKey();
        $this->assertIsString($key);
        $this->assertSame(0, $ref->getSnapshot()->numChildren());
    }
    public function setWithNullIsSameAsRemove(): void
    {
        $ref = $this->ref->getChild(__FUNCTION__);
        $key = $ref->push('foo')->getKey();
        $this->assertSame(1, $ref->getSnapshot()->numChildren());
        $this->assertNotNull($key);
        $ref->getChild($key)->set(null);
        $this->assertSame(0, $ref->getSnapshot()->numChildren());
    }
    public function setServerTimestamp(): void
    {
        $value = $this->ref->getChild(__FUNCTION__)
            ->push(['updatedAt' => Database::SERVER_TIMESTAMP])
            ->getSnapshot()->getValue()
        ;
        $this->assertIsArray($value);
        $this->assertArrayHasKey('updatedAt', $value);
        $this->assertIsInt($value['updatedAt']);
    }
    public static function validValues(): Iterator
    {
        yield 'string' => ['string', 'value'];
        yield 'int' => ['int', 1];
        yield 'bool_true' => ['true', true];
        yield 'bool_false' => ['false', false];
        yield 'array' => ['array', ['first' => 'value', 'second' => 'value']];
    }
}
