<?php
declare(strict_types = 1);
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */
namespace Wdes\PIL\Twig;

use \Twig\Node\Node;
use \Twig\Node\Expression\AbstractExpression;
use \Twig\Compiler;
use Wdes\PIL\Node\TransNode;

/**
 * Translation node for Twig
 * @license MPL-2.0
 */
class TranslationNode extends TransNode
{

    /**
     * Create a new TranslationNode
     *
     * @param Node               $body    Body of the node
     * @param Node               $plural  Node plural
     * @param AbstractExpression $count   Number of nodes
     * @param Node               $context Context
     * @param Node               $notes   Notes for node
     * @param int                $lineno  The line number
     * @param string             $tag     Node tag name
     */
    public function __construct(
        Node $body,
        Node $plural = null,
        AbstractExpression $count = null,
        Node $context = null,
        Node $notes = null,
        $lineno,
        $tag = null
    ) {
        $nodes = ['body' => $body];
        if (null !== $context) {
            $nodes['context'] = $context;
        }
        if (null !== $count) {
            $nodes['count'] = $count;
        }
        if (null !== $notes) {
            $nodes['notes'] = $notes;
        }
        if (null !== $plural) {
            $nodes['plural'] = $plural;
        }

        Node::__construct($nodes, [], $lineno, $tag);
    }

    /**
     * Compiles the node to PHP.
     *
     * @param Compiler $compiler Node compiler
     * @return void
     */
    public function compile(Compiler $compiler): void
    {
        $pMessage = null;
        $compiler->addDebugInfo($this);
        list($msg, $vars) = $this->compileString($this->getNode('body'));
        if ($this->hasNode('plural')) {
            list($pMessage, $Pvars) = $this->compileString($this->getNode('plural'));
            $vars                   = array_merge($vars, $Pvars);
        }

        $prefix = '\Wdes\PIL\Launcher::getPlugin()->';
        if ($this->hasNode('context')) {
            $function = $this->hasNode('plural') ? 'ngettext' : 'pgettext';
        } else {
            $function = $this->hasNode('plural') ? 'ngettext' : 'gettext';
        }
        $function = $prefix.$function;

        if ($this->hasNode('notes')) {
            $message = trim($this->getNode('notes')->getAttribute('data'));
            // line breaks are removed
            $message = str_replace(["\n", "\r"], ' ', $message);
            $compiler->write("// l10n: {$message}\n");
        }
        if ($vars) {
            $compiler->write('echo strtr('.$function.'(')->subcompile($msg);
            if ($this->hasNode('plural')) {
                $compiler->raw(', ')->subcompile($pMessage)->raw(', abs(')->subcompile($this->hasNode('count') ? $this->getNode('count') : null)->raw(')');
            }
            $compiler->raw('), [');
            $lastKey = key(array_slice($vars, -1));
            foreach ($vars as $key => $var) {
                $attrName = $var->getAttribute('name');
                if ($attrName === 'count') {
                    $compiler->string('%count%')->raw(' => abs(');
                    if ($this->hasNode('count')) {
                        $compiler->subcompile($this->getNode('count'));
                    }
                    if ($key === $lastKey) {
                        $compiler->raw(')');
                    } else {
                        $compiler->raw('), ');
                    }
                } else {
                    if ($key === $lastKey) {
                        $compiler->string('%'.$attrName.'%')->raw(' => ')->subcompile($var);
                    } else {
                        $compiler->string('%'.$attrName.'%')->raw(' => ')->subcompile($var)->raw(', ');
                    }
                }
            }
            $compiler->raw("]);\n");
        } else {
            $compiler->write('echo '.$function.'(');
            if ($this->hasNode('context')) {
                $context = trim($this->getNode('context')->getAttribute('data'));
                $compiler->raw('"'.$context.'", ');
            }
            $compiler->subcompile($msg);
            if ($this->hasNode('plural')) {
                $compiler->raw(', ')->subcompile($pMessage)->raw(', abs(')->subcompile($this->hasNode('count') ? $this->getNode('count') : null)->raw(')');
            }
            $compiler->raw(");\n");
        }
    }

}
