<?php

namespace Umanit\EasyAdminTreeBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[Gedmo\Tree(type: 'nested')]
#[ORM\MappedSuperclass]
abstract class AbstractTreeItem
{
    #[Gedmo\TreeLeft]
    #[ORM\Column(name: 'lft', type: Types::INTEGER)]
    protected $lft;

    #[Gedmo\TreeLevel]
    #[ORM\Column(name: 'lvl', type: Types::INTEGER)]
    protected $lvl;

    #[Gedmo\TreeRight]
    #[ORM\Column(name: 'rgt', type: Types::INTEGER)]
    protected $rgt;

    #[Gedmo\TreeRoot]
    #[ORM\JoinColumn(name: 'root_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $root;

    #[Gedmo\TreeParent]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $parent;

    #[ORM\OrderBy(['lft' => 'ASC'])]
    protected $children;

	abstract public function getId();

	abstract public function getName(): string;

    abstract public function setName(string $name): static;

	public function getRoot(): ?static
    {
        return $this->root;
    }

	public function setParent(self $parent = null): static
    {
        $this->parent = $parent;

		return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function getChildren()
    {
        return $this->children;
    }

	public function setChildren($children): static
    {
        $this->children = $children;

		return $this;
    }

    public function getLevel()
    {
        return $this->lvl;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
