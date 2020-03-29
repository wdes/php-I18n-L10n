<?php
declare(strict_types = 1);
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */
namespace Wdes\phpI18nL10n\Twig;

use Wdes\phpI18nL10n\Twig\TranslationNode;
use Twig\Error\SyntaxError;
use Twig\Node\Expression\NameExpression;
use Twig\Node\Node;
use Twig\Node\PrintNode;
use Twig\Node\TextNode;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

/**
 * Token parser for Twig
 * @license MPL-2.0
 */
class TokenParser extends AbstractTokenParser
{

    /**
     * Parses a token and returns a node.
     *
     * @param Token $token Twig token to parse
     * @return TranslationNode
     */
    public function parse(Token $token)
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
    public function decideForFork(Token $token)
    {
        return $token->test(
            ['plural', 'context', 'notes', 'endtrans']
        );
    }

    /**
     * @param Token $token The token to test
     * @return bool
     */
    public function decideForEnd(Token $token)
    {
        return $token->test('endtrans');
    }

    /**
     * @return string
     */
    public function getTag()
    {
        return 'trans';
    }

    /**
     * @param Node $body   The body node
     * @param int  $lineNo The line number
     * @return void
     */
    protected function checkTransString(Node $body, $lineNo)
    {
        foreach ($body as $i => $node) {
            if ($node instanceof TextNode || ($node instanceof PrintNode && $node->getNode('expr') instanceof NameExpression)) {
                continue;
            }

            throw new SyntaxError('The text to be translated with "trans" can only contain references to simple variables', $lineNo);
        }
    }

}
