<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Entity;

/**
 * Class EventListener
 * @package Fraym\Entity
 * @Injectable(lazy=true)
 */
class EventListener
{
    /**
     * @Inject
     * @var \Fraym\Database\Database
     */
    protected $db;

    /**
     * @Inject
     * @var \Fraym\ServiceLocator\ServiceLocator
     */
    protected $serviceLocator;

    /**
     * Callback for events with \Doctrine\ORM\Event\LifecycleEventArgs arguments
     *
     * @param $event
     * @param $arguments
     */
    public function __call($event, $arguments)
    {
        /**
         * @var \Doctrine\ORM\Event\LifecycleEventArgs $eventArgs
         */
        $eventArgs = $arguments[0];
        $entity = $eventArgs->getEntity();
        $lifecycleCallbacks = $this->db->getAnnotationReader()->getClassAnnotation(
            new \ReflectionClass($entity),
            'Fraym\Annotation\LifecycleCallback'
        );

        if (is_object($lifecycleCallbacks)) {
            foreach ($lifecycleCallbacks as $lifecycleEvent => $lifecycleCallback) {
                if ($event === $lifecycleEvent &&
                    count($lifecycleCallback)
                ) {
                    foreach ($lifecycleCallback as $class => $method) {
                        $this->serviceLocator->get($class)->{$method}($entity, $eventArgs, $event);
                    }
                }
            }
        }
    }

    /**
     * @param \Doctrine\ORM\Event\OnFlushEventArgs $eventArgs
     */
    public function onFlush(\Doctrine\ORM\Event\OnFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $lifecycleCallbacks = $this->db->getAnnotationReader()->getClassAnnotation(
                new \ReflectionClass($entity),
                'Fraym\Annotation\LifecycleCallback'
            );
            if (is_object($lifecycleCallbacks)) {
                foreach ($lifecycleCallbacks as $lifecycleEvent => $lifecycleCallback) {
                    if (__FUNCTION__ === $lifecycleEvent &&
                        count($lifecycleCallback)
                    ) {
                        foreach ($lifecycleCallback as $class => $method) {
                            $this->serviceLocator->get($class)->{$method}($entity, $eventArgs, __FUNCTION__);
                        }
                    }
                }
            }
        }
    }
}
