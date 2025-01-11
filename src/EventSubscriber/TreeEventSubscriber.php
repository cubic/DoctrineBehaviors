<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\EventSubscriber;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Gedmo\Mapping\MappedEventSubscriber;
use Knp\DoctrineBehaviors\Contract\Entity\TreeNodeInterface;

final class TreeEventSubscriber extends MappedEventSubscriber
{
    public function loadClassMetadata(LoadClassMetadataEventArgs $loadClassMetadataEventArgs): void
    {
        $classMetadata = $loadClassMetadataEventArgs->getClassMetadata();
        if ($classMetadata->reflClass === null) {
            // Class has not yet been fully built, ignore this event
            return;
        }

        if (! is_a($classMetadata->reflClass->getName(), TreeNodeInterface::class, true)) {
            return;
        }

        if ($classMetadata->hasField('materializedPath')) {
            return;
        }

        $classMetadata->mapField([
            'fieldName' => 'materializedPath',
            'type' => 'string',
            'length' => 255,
        ]);
    }

    /**
     * @return string[]
     */
    public function getSubscribedEvents(): array
    {
        return [Events::loadClassMetadata];
    }


	protected function getNamespace()
	{
		return __NAMESPACE__;
	}
}
