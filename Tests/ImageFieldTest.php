<?php

/*
 * This file is part of Mindy Framework.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Tests;

use Mindy\Orm\Fields\ImageField;
use PHPUnit\Framework\TestCase;

class ImageFieldTest extends TestCase
{
    public function testImageField()
    {
        $field = new ImageField([
            'null' => false,
        ]);
        $this->assertTrue($field->isRequired());
        $this->assertCount(3, $field->getValidationConstraints());

        $field = new ImageField([
            'null' => true,
        ]);
        $this->assertFalse($field->isRequired());
        $this->assertCount(2, $field->getValidationConstraints());
    }
}
