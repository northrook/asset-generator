<?php

declare(strict_types=1);

namespace Core\Assets\Exception;

use Core\Assets\Factory\Asset\Type;
use InvalidArgumentException;

final class InvalidAssetTypeException extends InvalidArgumentException
{
    public readonly string $type;

    public function __construct( Type $type, ?string $message = null )
    {
        $this->type = $type->name;

        $message ??= \sprintf( 'Invalid asset type: %s', $this->type );

        parent::__construct( $message, 500 );
    }
}
