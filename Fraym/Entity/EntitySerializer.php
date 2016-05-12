<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManager;

/**
 * Class EntitySerializer
 * @package Fraym\Entity
 */
class EntitySerializer
{
    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var int
     */
    protected $entityRecursionDepth = 0;

    /**
     * @var int
     */
    protected $maxEntityRecursionDepth = 0;

    /**
     * @var int
     */
    protected $maxRecursionDepth = 0;

    /**
     * @param $em
     */
    public function __construct($em)
    {
        $this->setEntityManager($em);
    }

    /**
     * @return Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->em;
    }

    /**
     * @param EntityManager $em
     * @return $this
     */
    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
        return $this;
    }

    /**
     * @param $entity
     * @param array $parentEntities
     * @param int $currentDepth
     * @return array
     */
    protected function serializeEntity($entity, $parentEntities = [], $currentDepth = 0)
    {
        $className = get_class($entity);
        $metadata = $this->em->getClassMetadata($className);
        $data = [];
        $currentDepth++;

        if (($this->maxRecursionDepth === 0 || ($this->maxRecursionDepth >= $currentDepth))) {
            foreach ($metadata->fieldMappings as $field => $mapping) {
                $value = $metadata->reflFields[$field]->getValue($entity);
                if ($value instanceof \DateTime) {
                    // We cast DateTime to array to keep consistency with array result
                    $data[$field] = (array)$value;
                } elseif (is_object($value)) {
                    $data[$field] = (string)$value;
                } else {
                    $data[$field] = $value;
                }
            }

            foreach ($metadata->associationMappings as $field => $mapping) {
                if ($mapping['targetEntity'] != $className) {
                    $parentEntities[] = get_class($entity);
                }

                if (!in_array($mapping['targetEntity'], $parentEntities)) {
                    $key = $field;

                    if ($mapping['isCascadeDetach'] || $mapping['type'] == ClassMetadata::MANY_TO_MANY) {
                        if ('Doctrine\ORM\PersistentCollection' == get_class($entity->$field)) {
                            if (!in_array(
                                $mapping['targetEntity'],
                                $parentEntities
                            ) &&
                                ($this->maxRecursionDepth === 0 || $this->maxRecursionDepth > $currentDepth)
                            ) {
                                $data[$key] = [];
                                $parentEntities[] = $mapping['targetEntity'];
                                foreach ($entity->$field as $child) {
                                    $data[$key][] = $this->serializeEntity($child, $parentEntities, $currentDepth);
                                }
                            }
                        } else {
                            $data[$key] = $metadata->reflFields[$field]->getValue($entity);
                            if (null !== $data[$key]) {
                                $data[$key] = $this->serializeEntity($data[$key], $parentEntities, $currentDepth);
                            }
                        }
                    } elseif ($mapping['isOwningSide'] &&
                        in_array(
                            $mapping['type'],
                            [ClassMetadata::TO_ONE, ClassMetadata::MANY_TO_ONE]
                        )
                    ) {
                        if (null !== $metadata->reflFields[$field]->getValue($entity)) {
                            if ($this->entityRecursionDepth < $this->maxEntityRecursionDepth
                                || $this->maxEntityRecursionDepth === 0
                            ) {
                                $this->entityRecursionDepth++;
                                $parentEntities[] = $mapping['targetEntity'];
                                $data[$key] = $this->serializeEntity(
                                    $metadata->reflFields[$field]
                                        ->getValue($entity),
                                    $parentEntities,
                                    $currentDepth
                                );
                                $this->entityRecursionDepth--;
                            } else {
                                $data[$key] = $this->getEntityManager()
                                    ->getUnitOfWork()
                                    ->getEntityIdentifier(
                                        $metadata->reflFields[$field]
                                            ->getValue($entity)
                                    );
                            }
                        } else {
                            // In some case the relationship may not exist, but we want
                            // to know about it
                            $data[$key] = null;
                        }
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @param $parentEntities
     * @param $className
     * @return mixed
     */
    private function removeClassNameFromArray($parentEntities, $className)
    {
        foreach ($parentEntities as $k => $val) {
            if ($val === $className) {
                unset($parentEntities[$k]);
            }
        }
        return $parentEntities;
    }

    /**
     * Serialize an entity to an array
     *
     * @param The entity $entity
     * @return array
     */
    public function toArray($entity)
    {
        return $this->serializeEntity($entity);
    }


    /**
     * Convert an entity to a JSON object
     *
     * @param The entity $entity
     * @return string
     */
    public function toJson($entity)
    {
        return json_encode($this->toArray($entity));
    }

    /**
     * Set the maximum recursion depth of a entity
     *
     * @param   int $maxRecursionDepth
     * @return  void
     */
    public function setMaxEntityRecursionDepth($maxRecursionDepth)
    {
        $this->maxEntityRecursionDepth = $maxRecursionDepth;
    }

    /**
     * Get the maximum recursion depth of a entity
     *
     * @return  int
     */
    public function getMaxEntityRecursionDepth()
    {
        return $this->maxEntityRecursionDepth;
    }

    /**
     * Set the maximum recursion depth
     *
     * @param   int $maxRecursionDepth
     * @return  void
     */
    public function setMaxRecursionDepth($maxRecursionDepth)
    {
        $this->maxRecursionDepth = $maxRecursionDepth;
    }

    /**
     * Get the maximum recursion depth
     *
     * @return  int
     */
    public function getMaxRecursionDepth()
    {
        return $this->maxRecursionDepth;
    }
}
