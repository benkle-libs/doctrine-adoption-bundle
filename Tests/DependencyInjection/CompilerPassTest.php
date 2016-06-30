<?php
/**
 * Copyright (c) 2016 Benjamin Kleiner
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of
 * the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS
 * OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Benkle\DoctrineAdoptionBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class CompilerPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $self = $this;
        $container = $this->createMock(ContainerBuilder::class);
        $collectorDef = $this->createMock(Definition::class);
        $container
            ->expects($this->atLeast(1))
            ->method('findDefinition')
            ->with('benkle.doctrine.adoption.collector')
            ->willReturn($collectorDef);
        $container
            ->expects($this->atLeast(1))
            ->method('findTaggedServiceIds')
            ->with('benkle.doctrine.adoption.child')
            ->willReturn(
                [
                    'test.adoptee.1' => [
                        [
                            'of'            => 'test.adopter',
                            'discriminator' => 'child1',
                        ],
                    ],
                    'test.adoptee.2' => [
                        [
                            'of'            => 'test.adopter',
                            'discriminator' => 'child2',
                        ],
                    ],
                ]
            );
        $container
            ->expects($this->exactly(2))
            ->method('getDefinition')
            ->with($this->logicalOr(
                'test.adoptee.1',
                'test.adoptee.2'
            ))
            ->will($this->returnCallback(function($serviceId) use ($self) {
                $mock = $self->createMock(Definition::class);
                $mock
                    ->expects($self->exactly(1))
                    ->method('getClass')
                    ->willReturn($serviceId);
                return $mock;
            }));
        $collectorDef
            ->expects($this->exactly(2))
            ->method('addMethodCall')
            ->with(
                'addAdoptee',
                $this->logicalOr(
                ['test.adopter', 'test.adoptee.1', 'child1'],
                ['test.adopter', 'test.adoptee.2', 'child2']
            ));
        $compilerPass = new CompilerPass();
        $compilerPass->process($container);
    }
}
