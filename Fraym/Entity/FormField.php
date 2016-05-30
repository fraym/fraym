<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Entity;
use Fraym\Validation\Validation;

/**
 * Class FormField
 * @package Fraym\Entity
 * @Injectable(lazy=true)
 */
class FormField
{
    /**
     * @var null
     */
    private $_model = null;

    /**
     * @Inject
     * @var \Fraym\Translation\Translation
     */
    protected $translation;

    /**
     * @Inject
     * @var \Fraym\Database\Database
     */
    protected $db;

    /**
     * @param $model
     * @return $this
     */
    public function setClassName($model)
    {
        $this->_model = $model;
        return $this;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        $em = $this->db;
        $annotationReader = $em->getAnnotationReader();
        $tmpFieldMappings = $em->getClassMetadata($this->_model)->fieldMappings;
        $tmpAssocMappings = $em->getClassMetadata($this->_model)->associationMappings;
        $formfields = [];

        foreach ($tmpFieldMappings as $propertyName => $value) {
            $property = new \ReflectionProperty($this->_model, $propertyName);
            $classAnnotations = $annotationReader->getPropertyAnnotations($property);
            $formFieldAnnotation = $this->getAnnotation($classAnnotations);
            if ($formFieldAnnotation) {
                $formfields[$propertyName] = $this->buildFormFieldArray($propertyName, $formFieldAnnotation);
                $formfields[$propertyName]['annotations'] = $classAnnotations;
                $formFieldAnnotationTranslation = $this->getAnnotation(
                    $classAnnotations,
                    'Gedmo\Mapping\Annotation\Translatable'
                );
                if (false !== $formFieldAnnotationTranslation) {
                    $formfields[$propertyName]['translateable'] = true;
                } else {
                    $formfields[$propertyName]['translateable'] = false;
                }
            }
        }

        foreach ($tmpAssocMappings as $propertyName => $value) {
            $property = new \ReflectionProperty($this->_model, $propertyName);
            $classAnnotations = $annotationReader->getPropertyAnnotations($property);
            $formFieldAnnotation = $this->getAnnotation($classAnnotations);
            if ($formFieldAnnotation) {
                $formFieldAnnotation = $this->buildFormFieldArray($propertyName, $formFieldAnnotation);
                $sort = isset($formFieldAnnotation['sort']) ? $formFieldAnnotation['sort'] : [];
                $formFieldAnnotation['annotations'] = $classAnnotations;
                $formFieldAnnotation['options'] = $em->getRepository($value['targetEntity'])->findBy([], $sort);
                $formFieldAnnotation['model'] = '\\' . ltrim($value['targetEntity'], '\\');
                $formFieldAnnotation['translateable'] = false;
                $formfields[$propertyName] = $formFieldAnnotation;
            }
        }
        return $formfields;
    }

    /**
     * @param $field
     * @param $formfield
     * @return array
     */
    private function buildFormFieldArray($field, $formfield)
    {
        $formfield = (array)$formfield;
        $formfield['label'] = $this->getLabel($field, $formfield['label']);
        $formfield['hasValidation'] = function ($validation) use (&$formfield) {
            return in_array($validation, $formfield['validation']);
        };
        return $formfield;
    }

    /**
     * @param $fieldValue
     * @param $model
     * @param $fieldName
     * @return bool
     */
    public function uniqueEntityCheck($fieldValue, $model, $fieldName)
    {
        return $this->db->getRepository($model)->findOneBy([$fieldName => $fieldValue]) === null;
    }

    /**
     * @param $prop
     * @param string $defaultLabelText
     * @return mixed
     */
    private function getLabel($prop, $defaultLabelText = '')
    {
        $className = str_ireplace('\\', '_', $this->_model);
        return $this->translation->getTranslation(
            $defaultLabelText,
            'FIELD_LABEL_' . strtoupper($className) . '_' . strtoupper($prop)
        );
    }

    /**
     * @param $prop
     * @param $value
     * @param $modelName
     * @param $validationRule
     * @return mixed
     */
    public function getErrorMessage($prop, $value, $modelName, $validationRule)
    {
        $className = str_ireplace('\\', '_', $modelName);
        $messages = Validation::DEFAULT_ERROR_TRANSLATIONS;
        $rule = strtoupper($validationRule);
        $key = 'FIELD_ERROR' . strtoupper($className) . '_' . strtoupper($prop) . '_' . $rule;
        
        $default = isset($messages[$rule]) ? $messages[strtoupper($validationRule)] : $key;

        return $this->translation->getTranslation(
            $default,
            $key
        );
    }

    /**
     * @param $classAnnotations
     * @param string $class
     * @return bool
     */
    public function getAnnotation($classAnnotations, $class = 'Fraym\Annotation\FormField')
    {
        foreach ($classAnnotations as $annotation) {
            if (get_class($annotation) === $class) {
                return $annotation;
            }
        }
        return false;
    }
}
