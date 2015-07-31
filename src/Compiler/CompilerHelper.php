<?php
namespace Rested\Compiler;

use SuperClosure\Analyzer\AstAnalyzer;
use SuperClosure\Analyzer\TokenAnalyzer;
use SuperClosure\SerializableClosure;
use SuperClosure\Serializer;

class CompilerHelper
{

    private static $serializer;

    public static function serialize(array $attributes, $ignoreFields = [])
    {
        if (static::$serializer === null) {
            static::$serializer = new Serializer(new AstAnalyzer());
        }

        $data = [];

        foreach ($attributes as $k => $v) {
            if (in_array($k, $ignoreFields) === true) {
                continue;
            } else if ($v instanceof \Closure) {
                $data[$k] = new SerializableClosure($v, static::$serializer);
            } else {
                $data[$k] = $v;
            }
        }

        return serialize($data);
    }
}
