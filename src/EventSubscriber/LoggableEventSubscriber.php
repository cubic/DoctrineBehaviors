<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\EventSubscriber;

use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Gedmo\Mapping\MappedEventSubscriber;
use Knp\DoctrineBehaviors\Contract\Entity\LoggableInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

final class LoggableEventSubscriber extends MappedEventSubscriber
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    public function postPersist(PostPersistEventArgs $lifecycleEventArgs): void
    {
        $entity = $lifecycleEventArgs->getObject();
        if (! $entity instanceof LoggableInterface) {
            return;
        }

        $createLogMessage = $entity->getCreateLogMessage();
        $this->logger->log(LogLevel::INFO, $createLogMessage);

        $this->logChangeSet($lifecycleEventArgs);
    }

    public function postUpdate(PostUpdateEventArgs $lifecycleEventArgs): void
    {
        $entity = $lifecycleEventArgs->getObject();
        if (! $entity instanceof LoggableInterface) {
            return;
        }

        $this->logChangeSet($lifecycleEventArgs);
    }

    public function preRemove(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $entity = $lifecycleEventArgs->getObject();

        if ($entity instanceof LoggableInterface) {
            $this->logger->log(LogLevel::INFO, $entity->getRemoveLogMessage());
        }
    }

    /**
     * @return string[]
     */
    public function getSubscribedEvents(): array
    {
        return [Events::postPersist, Events::postUpdate, Events::preRemove];
    }

    /**
     * Logs entity changeset
     */
    private function logChangeSet(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $entityManager = $lifecycleEventArgs->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();
        $entity = $lifecycleEventArgs->getEntity();

        $entityClass = $entity::class;
        $classMetadata = $entityManager->getClassMetadata($entityClass);

        /** @var LoggableInterface $entity */
        $unitOfWork->computeChangeSet($classMetadata, $entity);
        $changeSet = $unitOfWork->getEntityChangeSet($entity);

        $message = $entity->getUpdateLogMessage($changeSet);

        if ($message === '') {
            return;
        }

        $this->logger->log(LogLevel::INFO, $message);
    }


	protected function getNamespace()
	{
		return __NAMESPACE__;
	}
}
