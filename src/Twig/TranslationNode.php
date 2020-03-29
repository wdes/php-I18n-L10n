<?php
declare(strict_types = 1);
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace Wdes\phpI18nL10n\Twig;

use Twig\Compiler;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\FilterExpression;
use Twig\Node\Expression\NameExpression;
use Twig\Node\Expression\TempNameExpression;
use Twig\Node\Node;
use Twig\Node\PrintNode;

/**
 * Translation node for Twig
 * @license MPL-2.0
 */
class TranslationNode extends Node
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

        parent::__construct($nodes, [], $lineno, $tag);
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

        $prefix = '\Wdes\phpI18nL10n\Launcher::getPlugin()->';
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
            $compiler->write('echo strtr('.$function.'(');
            if ($this->hasNode('context')) {
                $context = trim($this->getNode('context')->getAttribute('data'));
                $compiler->raw('"'.$context.'", ');
            }
            $compiler->subcompile($msg);
            if ($this->hasNode('plural')) {
                $compiler->raw(', ')->subcompile($pMessage)->raw(', abs(')->subcompile($this->hasNode('count') ? $this->getNode('count') : null)->raw(')');
            }
            $compiler->raw('), [');
            $this->compileVarsArgs($compiler, $vars);
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

    /**
     * @param Compiler         $compiler The template compiler
     * @param NameExpression[] $vars     The variables
     * @return void
     */
    public function compileVarsArgs(Compiler $compiler, array $vars): void
    {
        $lastKey = array_keys($vars)[count($vars) - 1] ?? null;
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
    }

    /**
     * Compile a translation string
     *
     * @author Fabien Potencier <fabien.potencier@symfony-project.com>
     * @param Node $body A Node instance
     *
     * @return array
     */
    protected function compileString(Node $body)
    {
        if ($body instanceof NameExpression || $body instanceof ConstantExpression || $body instanceof TempNameExpression) {
            return [$body, []];
        }

        $vars = [];
        if (count($body)) {
            $msg = '';

            foreach ($body as $node) {
                if ($node instanceof PrintNode) {
                    $n = $node->getNode('expr');
                    while ($n instanceof FilterExpression) {
                        $n = $n->getNode('node');
                    }
                    $msg   .= sprintf('%%%s%%', $n->getAttribute('name'));
                    $vars[] = new NameExpression($n->getAttribute('name'), $n->getTemplateLine());
                } else {
                    $msg .= $node->getAttribute('data');
                }
            }
        } else {
            $msg = $body->getAttribute('data');
        }

        return [new Node([new ConstantExpression(trim($msg), $body->getTemplateLine())]), $vars];
    }

}
