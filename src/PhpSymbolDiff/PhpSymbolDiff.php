<?php
/*
 * This file is part of the civicrm-cxn-rpc package.
 *
 * Copyright (c) Tim Otten <to-git@think.hm>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this package.
 */

namespace PhpSymbolDiff;

use PhpParser\Node\Expr;

class PhpSymbolDiff {

  protected $oldSrc, $newSrc;
  protected $oldLines, $newLines;

  public function __construct($oldSrc, $newSrc) {
    $this->newSrc = $newSrc;
    $this->oldSrc = $oldSrc;
    $this->oldLines = explode("\n", $oldSrc);
    $this->newLines = explode("\n", $newSrc);
  }


  public function findChangedSymbols() {
    $parser = new \PhpParser\Parser(new \PhpParser\Lexer());
    $oldStmts = $parser->parse($this->oldSrc);
    $newStmts = $parser->parse($this->newSrc);

    $oldSymbols = $this->createSymbolTable($oldStmts);
    $newSymbols = $this->createSymbolTable($newStmts);

    $commonSymbols = array_intersect(array_keys($oldSymbols), array_keys($newSymbols));

    // Wholesale additions and removals.
    $changedSymbols = array_diff(array_keys($oldSymbols), $commonSymbols) + array_diff(array_keys($newSymbols), $commonSymbols);

    // Incremental/internal changes
    foreach ($commonSymbols as $commonSymbol) {
      if (!$this->isEqual($oldSymbols[$commonSymbol], $newSymbols[$commonSymbol])) {
        $changedSymbols[] = $commonSymbol;
      }
    }

    sort($changedSymbols);

    return $changedSymbols;
  }

  /**
   * @return array
   *   Array(string $symbol => Node)
   */
  public function createSymbolTable($stmts) {
    $symbols = array();
    foreach ($stmts as $parentStmt) {
      if ($parentStmt instanceof \PhpParser\Node\Stmt\Class_) {
        $symbols[$parentStmt->name] = $parentStmt;
        foreach ($parentStmt->stmts as $childStmt) {
          if ($childStmt instanceof \PhpParser\Node\Stmt\ClassMethod) {
            $symbols[$parentStmt->name . '::' . $childStmt->name . '()'] = $childStmt;
          }
          elseif ($childStmt instanceof \PhpParser\Node\Stmt\Property) {
            foreach ($childStmt->props as $prop) {
              $synthProp = new \PhpParser\Node\Stmt\Property($childStmt->type, array($prop), $childStmt->getAttributes());
              $symbols[$parentStmt->name . '::$' . $prop->name] = $synthProp;
            }
          }
        }
      }
      elseif ($parentStmt instanceof \PhpParser\Node\Stmt\Namespace_) {
        if (!empty($parentStmt->name->parts)) {
          $nsName = implode("\\", $parentStmt->name->parts) . "\\";
        }
        else {
          $nsName = '';
        }
        $nsSymbols = $this->createSymbolTable($parentStmt->stmts);
        foreach ($nsSymbols as $nsSymbol => $childStmt) {
          $symbols["{$nsName}{$nsSymbol}"] = $childStmt;
        }
      }
      elseif ($parentStmt instanceof \PhpParser\Node\Stmt\Function_) {
        $symbols[$parentStmt->name . '()'] = $parentStmt;
      }
    }
    return $symbols;
  }

  protected function concatComments($comments) {
    $text = '';
    $comments = (array) $comments;
    foreach ($comments as $comment) {
      $text .= $comment->getText();
    }
    return $text;
  }

  protected function isEqual(\PhpParser\Node $old, \PhpParser\Node $new) {
    $oldLines = array_slice($this->oldLines, $old->getAttribute('startLine') - 1, $old->getAttribute('endLine') - $old->getAttribute('startLine') + 1);
    $newLines = array_slice($this->newLines, $new->getAttribute('startLine') - 1, $new->getAttribute('endLine') - $new->getAttribute('startLine') + 1);
    if ($oldLines != $newLines) {
      return FALSE;
    }

    $oldComments = $this->concatComments($old->getAttribute('comments'));
    $newComments = $this->concatComments($new->getAttribute('comments'));
    if ($oldComments != $newComments) {
      return FALSE;
    }

    return TRUE;
  }

}
