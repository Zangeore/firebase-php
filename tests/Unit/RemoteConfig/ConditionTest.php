<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\RemoteConfig;

use Kreait\Firebase\RemoteConfig\Condition;
use Kreait\Firebase\RemoteConfig\TagColor;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
final class ConditionTest extends UnitTestCase
{
    public function itCanBeNamed(): void
    {
        $condition = Condition::named('name');
        $this->assertSame('name', $condition->name());
    }

    public function itsDefaultExpressionIsFalseAsString(): void
    {
        $condition = Condition::named('name');
        $this->assertSame('false', $condition->expression());
    }

    public function itsDefaultTagColorIsNotSet(): void
    {
        $condition = Condition::named('name');
        $this->assertNull($condition->tagColor());
    }

    public function itsTagColorCanBeSetWithAString(): void
    {
        $condition = Condition::named('name')->withTagColor('ORANGE');
        $expectedColor = new TagColor('ORANGE');
        $this->assertNotNull($condition->tagColor());
        $this->assertSame($condition->tagColor()->value(), $expectedColor->value());
    }

    /**
     * @param array<mixed> $conditionData
     */
    public function itCanBeCreatedFromAnArray(string $expectedName, string $expectedExpression, ?TagColor $expectedTagColor, array $conditionData): void
    {
        $condition = Condition::fromArray($conditionData);
        $this->assertSame($expectedName, $condition->name());
        $this->assertSame($expectedExpression, $condition->expression());
        $this->assertSame(($nullsafeVariable1 = $expectedTagColor) ? $nullsafeVariable1->value() : null, ($nullsafeVariable2 = $condition->tagColor()) ? $nullsafeVariable2->value() : null);
    }

    /**
     * @return iterable<string, mixed>
     */
    public static function valueProvider(): iterable
    {
        yield 'color string' => [
            'My Name',
            'expression',
            new TagColor('GREEN'),
            [
                'name' => 'My Name',
                'expression' => 'expression',
                'tagColor' => 'GREEN',
            ],
        ];

        yield 'no color' => [
            'My Name',
            'expression',
            null,
            [
                'name' => 'My Name',
                'expression' => 'expression',
                'tagColor' => null,
            ],
        ];
    }
}
