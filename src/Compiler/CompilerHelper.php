<?php
namespace Rested\Compiler;

use SuperClosure\Analyzer\AstAnalyzer;
use SuperClosure\Serializer;

class CompilerHelper
{

    private static $serializer;

    public static function serialize(array $attributes, $ignoreFields = [])
    {
        $data = [];

        foreach ($attributes as $k => $v) {
            if (in_array($k, $ignoreFields) === true) {
                continue;
            } else if ($v instanceof \Closure) {
                $data[$k] = static::serializeClosure($v);
            } else {
                $data[$k] = $v;
            }
        }

        return serialize($data);
    }

    public static function serializeClosure(\Closure $closure)
    {
        if (static::$serializer === null) {
            static::$serializer = new Serializer(new AstAnalyzer());
        }

        return static::$serializer->serialize($closure);
    }
}
