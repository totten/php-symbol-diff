<?php
/*
 * This file is part of the php-symbol-diff package.
 *
 * Copyright (c) Tim Otten <to-git@think.hm>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this package.
 */

namespace PhpSymbolDiff;

class PhpSymbolDiffTest extends \PHPUnit_Framework_TestCase {

  public function diffCases() {
    $cases = array();
    $cases[] = array('global-base.txt', 'global-func.txt', array('globfunc()'));
    $cases[] = array('global-base.txt', 'global-foo-bar-doit.txt', array('Foo_Bar', 'Foo_Bar::doIt()'));
    $cases[] = array('global-base.txt', 'global-foo-bar-doit-rm.txt', array('Foo_Bar', 'Foo_Bar::doIt()'));
    $cases[] = array('global-base.txt', 'global-foo-bar-frobnicate.txt', array('Foo_Bar', 'Foo_Bar::frobnicate()'));

    $cases[] = array('ns-base.txt', 'ns-func.txt', array('Foo\theMain()'));
    $cases[] = array('ns-base.txt', 'ns-func-comment.txt', array('Foo\theMain()'));
    $cases[] = array('ns-base.txt', 'ns-bar-doit.txt', array('Foo\Bar', 'Foo\Bar::doIt()'));
    $cases[] = array('ns-base.txt', 'ns-bar-doit-comment.txt', array('Foo\Bar', 'Foo\Bar::doIt()'));
    $cases[] = array('ns-base.txt', 'ns-bar-data.txt', array('Foo\Bar', 'Foo\Bar::$data'));
    $cases[] = array('ns-base.txt', 'ns-bar-data-comment.txt', array('Foo\Bar', 'Foo\Bar::$data'));
    return $cases;
  }

  /**
   * @dataProvider diffCases
   */
  public function testDiff($oldFile, $newFile, $expectedChanges) {
    $differ = new PhpSymbolDiff(file_get_contents(__DIR__ . '/data/' . $oldFile), file_get_contents(__DIR__ . '/data/' . $newFile));
    $actualChanges = $differ->findChangedSymbols();
    sort($actualChanges);
    $this->assertEquals($expectedChanges, $actualChanges);
  }

}
