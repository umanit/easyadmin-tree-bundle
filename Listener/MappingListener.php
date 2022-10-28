<?php

namespace Umanit\EasyAdminTreeBundle\Listener;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Umanit\EasyAdminTreeBundle\Entity\AbstractTreeItem;

class MappingListener
{
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {

        $classMetadata = $eventArgs->getClassMetadata();
        $class = $classMetadata->getName();

        if (is_subclass_of($class, AbstractTreeItem::class))
        {
            $fieldMapping = [
                'fieldName' => 'root',
                'targetEntity' => $class,
            ];
            $classMetadata->mapManyToOne($fieldMapping);


            $fieldMapping = [
                'fieldName' => 'parent',
                'targetEntity' => $class,
                'inversedBy' => 'children'
            ];
            $classMetadata->mapManyToOne($fieldMapping);

            $fieldMapping = [
                'fieldName' => 'children',
                'targetEntity' => $class,
                'mappedBy' => 'parent',
            ];
            $classMetadata->mapOneToMany($fieldMapping);
        }
    }
}