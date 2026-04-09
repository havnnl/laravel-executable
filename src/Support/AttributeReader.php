<?php

declare(strict_types=1);

namespace Havn\Executable\Support;

use Exception;
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

    /**
     * Mirrors Illuminate\Support\Traits\ReadsClassAttributes::getAttributeValue.
     *
     * @param  class-string  $attributeClass
     */
    public static function resolveValue(
        object $target,
        string $attributeClass,
        ?string $property = null,
        mixed $default = null,
    ): mixed {
        $reflection = new ReflectionClass($target);
        $defaultProperties = $reflection->getDefaultProperties();

        if ($property !== null
            && isset($target->{$property})
            && $target->{$property} !== ($defaultProperties[$property] ?? null)
        ) {
            return $target->{$property};
        }

        try {
            do {
                $attributes = $reflection->getAttributes($attributeClass);

                if (count($attributes) > 0) {
                    return self::extractAttributeValue($attributes[0]->newInstance());
                }
            } while ($reflection = $reflection->getParentClass());
        } catch (Exception) {
            //
        }

        return $target->{$property} ?? $default;
    }

    private static function extractAttributeValue(object $instance): mixed
    {
        $properties = get_object_vars($instance);

        return $properties === [] ? true : reset($properties);
    }
}
