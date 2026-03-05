<?php

declare(strict_types=1);

namespace Havn\Executable\Support;

use ReflectionClass;

/**
 * @internal
 */
final class AttributeReader
{
    /**
     * @template T of object
     *
     * @param  class-string<T>  $attributeClass
     * @return T|null
     */
    public static function firstFromClassHierarchy(object $target, string $attributeClass): ?object
    {
        $reflection = new ReflectionClass($target);

        do {
            $attributes = $reflection->getAttributes($attributeClass);

            if (! empty($attributes)) {
                return $attributes[0]->newInstance();
            }
        } while ($reflection = $reflection->getParentClass());

        return null;
    }
}
