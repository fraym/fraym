<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\EntityManager;

/**
 * Class EntityManager
 * @package Fraym\EntityManager
 * @Injectable(lazy=true)
 */
class EntityManager
{

    /**
     * @Inject
     * @var \Fraym\Database\Database
     */
    protected $db;

    /**
     * @Inject
     * @var \Fraym\Locale\Locale
     */
    protected $locale;

    /**
     * @param $entity
     * @param $propertyName
     * @param $locale
     * @param string $defaultValue
     * @return string
     */
    public function getEntityTranslation($entity, $propertyName, $locale, $defaultValue = '')
    {
        if (is_object($entity)) {
            $repository = $this->db->getRepository('\Gedmo\Translatable\Entity\Translation');
            $translations = $repository->findTranslations($entity);

            $property = new \ReflectionProperty($entity, $propertyName);
            $annotation = $this->db->getAnnotationReader()->getPropertyAnnotation($property, 'Doctrine\ORM\Mapping\Column');

            $value = isset($translations[$locale][$propertyName]) ? $translations[$locale][$propertyName] : $entity->$propertyName;

            if (is_string($value) && $annotation->type === 'array') {
                $value = unserialize($value);
            } elseif (is_string($value) && $annotation->type === 'date') {
                $value = \DateTime::createFromFormat('Y-m-d', $value);
            } elseif (is_string($value) && $annotation->type === 'datetime') {
                $value = \DateTime::createFromFormat('Y-m-d H:i:s', $value);
            } elseif (is_bool($value) === false && ($annotation->type === 'boolean' || $annotation->type === 'bool')) {
                $value = $value == 1 ? true : ($value === null ? null : false);
            }

            return $value;
        }

        return $defaultValue;
    }


    /**
     * @param $modelClassNameOrId
     * @return mixed
     */
    public function getEntityByStringOrId($modelClassNameOrId)
    {
        if (!is_numeric($modelClassNameOrId)) {
            $entity = $this->db->getRepository('\Fraym\EntityManager\Entity\Entity')->findOneByClassName(
                $modelClassNameOrId
            );
        } else {
            $entity = $this->db->getRepository('\Fraym\EntityManager\Entity\Entity')->findOneById($modelClassNameOrId);
        }
        return $entity;
    }
}
