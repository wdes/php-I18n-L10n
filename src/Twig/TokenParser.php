<?php
declare(strict_types = 1);
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */
namespace Wdes\PIL\Twig;

use \Twig\Token;
use \Twig\Extensions\TokenParser\TransTokenParser;
use \Twig\Extensions\Node\TransNode;
use \Wdes\PIL\Twig\TranslationNode;

/**
 * Token parser for Twig
 * @license MPL-2.0
 */
class TokenParser extends TransTokenParser
{

    /**
     * Parses a token and returns a node.
     *
     * @param Token $token Twig token to parse
     * @return TransNode
     */
    public function parse(Token $token): TransNode
    {
        $stream     = $this->parser->getStream();
        $pluralsNbr = null;
        $plural     = null;
        $notes      = null;
        $context    = null;
        if (!$stream->test(Token::BLOCK_END_TYPE)) {
            $body = $this->parser->getExpressionParser()->parseExpression();
        } else {
            $stream->expect(Token::BLOCK_END_TYPE);
            $body    = $this->parser->subparse(
                [$this, 'decideForFork']
            );
            $nextVal = $stream->next()->getValue();
            if ($nextVal === 'notes') {
                $stream->expect(Token::BLOCK_END_TYPE);
                $notes = $this->parser->subparse(
                    [$this, 'decideForEnd'], true
                );
            } elseif ($nextVal === 'plural') {
                $pluralsNbr = $this->parser->getExpressionParser()->parseExpression();
                $stream->expect(Token::BLOCK_END_TYPE);
                $plural = $this->parser->subparse(
                    [$this, 'decideForFork']
                );
                if ('notes' === $stream->next()->getValue()) {
                    $stream->expect(Token::BLOCK_END_TYPE);
                    $notes = $this->parser->subparse(
                        [$this, 'decideForEnd'], true
                    );
                }
            } elseif ($nextVal === 'context') {
                $stream->expect(Token::BLOCK_END_TYPE);
                $context = $this->parser->subparse(
                    [$this, 'decideForEnd'], true
                );
            }
        }
        $stream->expect(Token::BLOCK_END_TYPE);
        $lineNbr = $token->getLine();
        $this->checkTransString($body, $lineNbr);
        return new TranslationNode($body, $plural, $pluralsNbr, $context, $notes, $lineNbr, $this->getTag());
    }

    /**
     * Decides for fork
     *
     * @param Token $token Twig Token instance
     * @return bool
     */
    public function decideForFork(Token $token): bool
    {
        return $token->test(
            ['plural', 'context', 'notes', 'endtrans']
        );
    }

}
