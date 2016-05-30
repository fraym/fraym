<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Entity;

use \Doctrine\ORM\Mapping as ORM;

/**
 * Class BaseEntity
 * @package Fraym\Entity
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
class BaseEntity
{
    /**
     * @var null
     */
    public $serviceLocator = null;

    /**
     * @return \DI\Container
     */
    public function getServiceLocator()
    {
        global $diContainer;
        return $diContainer;
    }

    /**
     * @param $obj
     * @param bool $flush
     * @return bool
     */
    public function updateEntity($obj, $flush = true)
    {
        if (is_array($obj) === false && is_object($obj) === false) {
            return false;
        }

        try {
            $obj = (array)$obj;

            /**
             * @var \Fraym\Database\Database $em
             */
            $em = $this->getServiceLocator()->get('Fraym\Database\Database');
            /**
             * @var \Fraym\FormField\FormField $formField
             */
            $formField = $this->getServiceLocator()->get('Fraym\Entity\FormField');
            $className = get_class($this);
            $newEntity = $this;
            $tmpFieldMappings = $em->getClassMetadata($className)->fieldMappings;
            $tmpAssocMappings = $em->getClassMetadata($className)->associationMappings;
            $tmpAssocMappingClass = lcfirst(basename(str_replace('\\', '/', $className)));
            $formFields = $formField->setClassName($className)->getFields();

            foreach ($obj as $prop => $val) {
                if ($prop === 'id') {
                    continue;
                }

                if (isset($formFields[$prop]) && $formFields[$prop]['translateable'] === true && is_array($val)) {
                    $repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');
                    $newEntity->__set($prop, reset($val));
                    foreach ($val as $locale => $translation) {
                        $repository->translate($newEntity, $prop, $locale, $translation);
                    }
                } else {
                    if (is_string($val) || is_numeric($val)) {

                        /**
                         * Set string values
                         */
                        if (isset($tmpFieldMappings[$prop])) {
                            if ($tmpFieldMappings[$prop]['unique'] === true &&
                                $tmpFieldMappings[$prop]['nullable'] === true &&
                                $val === '') {
                                $val = null;
                            }
                            if (array_key_exists($prop, $tmpFieldMappings)) {
                                $newEntity->__set($prop, $val);
                            }
                        }

                        /**
                         * Set ManyToOne Entity
                         */
                        if (isset($tmpAssocMappings[$prop])) {
                            if (array_key_exists($prop, $tmpAssocMappings)) {
                                $targetEntityClass = $tmpAssocMappings[$prop]['targetEntity'];
                                $relationEntity = $em->getRepository($targetEntityClass)->findOneById($val);
                                if ($relationEntity) {
                                    $newEntity->__set($prop, $relationEntity);
                                }
                            }
                        }
                    }
                }
            }

            // clear all array collections
            foreach ($tmpAssocMappings as $prop => $val) {
                if (is_object($newEntity->{$prop}) &&
                    ('Doctrine\ORM\PersistentCollection' == get_class($newEntity->{$prop}) ||
                        'Doctrine\Common\Collections\ArrayCollection' == get_class($newEntity->{$prop}))
                ) {
                    $newEntity->{$prop}->clear();
                }
            }

            /**
             * OneToMany and ManyToMany
             */
            foreach ($obj as $prop => $val) {
                if (is_array($val) && isset($tmpAssocMappings[$prop])) {
                    $targetEntityClass = $tmpAssocMappings[$prop]['targetEntity'];
                    if (isset($newEntity->{$prop}) && $tmpAssocMappings[$prop]['type'] === 2) {
                        // ManyToOne update
                        $newEntity->{$prop}->updateEntity($val);
                    } else {
                        foreach ($val as $val2) {
                            if (is_array($val2) && isset($val2['id'])) {
                                $entity = $em->getRepository($targetEntityClass)->findOneById($val2['id']);
                                if ($entity) {
                                    $entity->updateEntity($val2);
                                }
                            } elseif (is_array($val2)) {
                                $newSubEntity = new $targetEntityClass();
                                $newSubEntity->{$tmpAssocMappingClass} = $newEntity;
                                $newSubEntity->updateEntity($val2);
                                $newEntity->{$prop}->add($newSubEntity);
                            } else {
                                $entity = $em->getRepository($targetEntityClass)->findOneById($val2);
                                if ($entity) {
                                    $newEntity->{$prop}->add($entity);
                                }
                            }
                        }
                    }
                }
            }

            if ($flush) {
                if (\Doctrine\ORM\UnitOfWork::STATE_MANAGED !== $em->getUnitOfWork()->getEntityState($newEntity) || \Doctrine\ORM\UnitOfWork::STATE_NEW !== $em->getUnitOfWork()->getEntityState($newEntity)) {
                    $em->persist($newEntity);
                }
                $em->flush();
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return true;
    }

    /**
     * @param int $maxRecursionDepth
     * @param int $maxEntityRecursionDepth
     * @return array
     */
    public function toArray($maxRecursionDepth = 0, $maxEntityRecursionDepth = 0)
    {
        return $this->getSerializer($maxRecursionDepth, $maxEntityRecursionDepth)->toArray($this);
    }

    /**
     * @param int $maxRecursionDepth
     * @param int $maxEntityRecursionDepth
     * @return array
     */
    public function toJson($maxRecursionDepth = 0, $maxEntityRecursionDepth = 0)
    {
        return $this->getSerializer($maxRecursionDepth, $maxEntityRecursionDepth)->toJson($this);
    }

    /**
     * @param $maxRecursionDepth
     * @param $maxEntityRecursionDepth
     * @return EntitySerializer
     */
    private function getSerializer($maxRecursionDepth, $maxEntityRecursionDepth)
    {
        /**
         * @var \Fraym\Database\Database $em
         */
        $em = $this->getServiceLocator()->get('Fraym\Database\Database');
        $em = $em->getEntityManager();
        $serializer = new EntitySerializer($em);
        $serializer->setMaxRecursionDepth($maxRecursionDepth);
        $serializer->setMaxEntityRecursionDepth($maxEntityRecursionDepth);
        return $serializer;
    }

    /**
     * @param array $array
     * @return $this
     */
    public function fromArray(array $array)
    {
        foreach ($array as $var => $val) {
            $this->__set($var, $val);
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        $ref = new \ReflectionClass($this);
        $properties = [];
        foreach ($ref->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED) as $prop) {
            $properties[] = $prop->name;
        }
        return $properties;
    }

    /**
     * @return array
     */
    public function getMethods()
    {
        $ref = new \ReflectionClass($this);
        $methods = [];
        foreach ($ref->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_PROTECTED) as $meth) {
            $methods[] = $meth->name;
        }
        return $methods;
    }

    /**
     * @param $var
     * @return null
     */
    public function __get($var)
    {
        $getter = 'get' . ucfirst($var);
        if (in_array($getter, $this->getMethods())) {
            return $this->$getter();
        }
        if (!in_array($var, $this->getProperties())) {
            return null;
        }
        return $this->$var;
    }

    /**
     * @param $var
     * @param $val
     * @return null
     */
    public function __set($var, $val)
    {
        $setter = 'set' . ucfirst($var);
        if (in_array($setter, $this->getMethods())) {
            return $this->$setter($val);
        }
        if (!in_array($var, $this->getProperties())) {
            return null;
        }
        $this->$var = $val;
    }

    /**
     * @param $entity
     * @return mixed
     */
    public function copyEntityTo($entity)
    {
        foreach ($this as $field => $val) {
            $entity->$field = $val;
        }
        return $entity;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return '';
    }
}
