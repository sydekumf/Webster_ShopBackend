<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Webster\Shop\Persistence;

use Doctrine\Common\Persistence\Mapping\Driver\AnnotationDriver as AbstractAnnotationDriver;
use Doctrine\Search\Mapping\Annotations as Search;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Search\Exception\Driver as DriverException;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;

/**
 * The AnnotationDriver reads the mapping metadata from docblock annotations.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       1.0
 * @author      Mike Lohmann <mike.h.lohmann@googlemail.com>
 */
class AnnotationDriver extends AbstractAnnotationDriver
{
    /**
     * Document fields annotation classes, ordered by precedence.
     */
    protected $entityFieldAnnotationClasses = array(
        'Doctrine\\ORM\\Mapping\\ManyToMany',
        'Doctrine\\ORM\\Mapping\\OneToMany',
        'Doctrine\\ORM\\Mapping\\ManyToOne'
    );

    /**
     * {@inheritDoc}
     *
     * @throws \ReflectionException
     */
    public function loadMetadataForClass($className, ClassMetadata $metadata)
    {
        $reflClass = $metadata->getReflectionClass();

        if (!$reflClass) {
            $reflClass = new \ReflectionClass((string)$className);
        }

        $reflProperties = $reflClass->getProperties();

        $this->extractPropertiesAnnotations($reflProperties, $metadata);
    }

    /**
     * Extract the property annotations.
     *
     * @param \ReflectionProperty[] $reflProperties
     * @param ClassMetadata         $metadata
     *
     * @return ClassMetadata
     */
    private function extractPropertiesAnnotations(array $reflProperties, ClassMetadata $metadata)
    {
        foreach ($reflProperties as $reflProperty) {
            foreach ($this->reader->getPropertyAnnotations($reflProperty) as $annotation) {
                foreach ($this->entityFieldAnnotationClasses as $fieldAnnotationClass) {
                    if ($annotation instanceof $fieldAnnotationClass) {
                        $metadata->addFieldMapping($reflProperty, $annotation);
                        continue 2;
                    }
                }
            }
        }

        return $metadata;
    }
}
