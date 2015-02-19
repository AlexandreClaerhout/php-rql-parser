<?php

namespace Graviton\Rql\Parser\Strategy;

use Graviton\Rql\Parser\ParserUtil;
use Graviton\Rql\AST\OperationFactory;
use Graviton\Rql\Lexer;

class LimitOperationStrategy extends ParsingStrategy
{
    /**
     * @return OperationInterface
     */
    public function parse()
    {
        $operation = OperationFactory::fromLexerToken($this->lexer->lookahead['type']);

        $fields = array();
        $limitDone = false;
        while (!$limitDone) {
            if ($this->lexer->lookahead == null) {
                $limitDone = true;
            } elseif ($this->lexer->lookahead['type'] == Lexer::T_INTEGER) {
                $fields[] = $this->lexer->lookahead['value'];
                $this->lexer->moveNext();
            } else {
                $this->lexer->moveNext();
            }
        }
        $operation->skip = $fields[0];
        $operation->limit = $fields[1];
        return $operation;
    }

    public function getAcceptedTypes()
    {
        return array(
            Lexer::T_LIMIT,
        );
    }
}
