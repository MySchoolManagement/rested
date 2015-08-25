<?php
namespace Rested\Compiler;

use SuperClosure\Analyzer\AstAnalyzer;
use SuperClosure\Analyzer\TokenAnalyzer;
use SuperClosure\SerializableClosure;
use SuperClosure\Serializer;

class CompilerHelper
{

    public static function serialize(array $attributes, $ignoreFields = [])
    {
        $data = [];
        $serializer = new Serializer(new AstAnalyzer());

        foreach ($attributes as $k => $v) {
            if (in_array($k, $ignoreFields) === true) {
                continue;
            } else if ($v instanceof \Closure) {
                $data[$k] = new SerializableClosure($v, $serializer);
            } else {
                $data[$k] = $v;
            }
        }

        return serialize($data);
    }
}
