<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

class ParameterValueType
{
    public const UNSPECIFIED = 'PARAMETER_VALUE_TYPE_UNSPECIFIED';
    public const STRING = 'STRING';
    public const BOOL = 'BOOLEAN';
    public const NUMBER = 'NUMBER';
    public const JSON = 'JSON';
}
