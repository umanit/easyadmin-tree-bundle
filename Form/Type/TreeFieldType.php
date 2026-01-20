<?php

namespace Umanit\EasyAdminTreeBundle\Form\Type;

use App\Repository\Client\CategoryRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Gedmo\Tree\Hydrator\ORM\TreeObjectHydrator;
use Symfony\Bridge\Doctrine\Form\ChoiceList\DoctrineChoiceLoader;
use Symfony\Bridge\Doctrine\Form\ChoiceList\ORMQueryBuilderLoader;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use TypeError;

class TreeFieldType extends AbstractType
{

	public function __construct(private readonly ManagerRegistry $registry) {}

	public function configureOptions(OptionsResolver $resolver): void
    {

		$choiceLoader = function (Options $options) {
			// Unless the choices are given explicitly, load them on demand
			if (null === $options['choices']) {
				// If there is no QueryBuilder we can safely cache
				$vary = [$options['em'], $options['class']];

				// also if concrete Type can return important QueryBuilder parts to generate
				// hash key we go for it as well, otherwise fallback on the instance
				if ($options['query_builder']) {
					$vary[] = $this->getQueryBuilderPartsForCachingHash($options['query_builder'])??$options['query_builder'];
				}

				return ChoiceList::loader($this, new DoctrineChoiceLoader(
					$options['em'],
					$options['class'],
					$options['id_reader'],
					new ORMQueryBuilderLoader(
						$options['query_builder']??$options['em']->getRepository($options['class'])->createQueryBuilder('e')
					)
				), $vary);
			}

			return null;
		};
        $resolver->setDefaults([
            'expanded'      => true,
            'block_name'    => 'umanit_easyadmin_tree',
			'query_builder' => function (CategoryRepository $er) {
                return $er
                    ->createQueryBuilder('entity')
                    ->orderBy('entity.root, entity.lft', 'ASC')
                ;
            },
			"choice_loader" => $choiceLoader,
            'choice_attr'   => function ($choice, $key, $value) {
                return ['data-level' => $choice->getLevel(), 'data-has-child' => !$choice->getChildren()->isEmpty()];
            },
//            'placeholder'   => 'umanit.easyadmin.tree.form-field.placeholder',
        ]);

    }

	public function getQueryBuilderPartsForCachingHash(object $queryBuilder): ?array {
		if (!$queryBuilder instanceof QueryBuilder) {
			throw new TypeError(sprintf('Expected an instance of "%s", but got "%s".', QueryBuilder::class, get_debug_type($queryBuilder)));
		}

		$this->registry->getManager()->getConfiguration()->addCustomHydrationMode('tree', TreeObjectHydrator::class);
		$query = $queryBuilder->getQuery()->setHint(Query::HINT_INCLUDE_META_COLUMNS, true);
		$query->getResult("tree");

		return [
			$query->getSQL(),
			array_map($this->parameterToArray(...), $queryBuilder->getParameters()->toArray()),
		];
	}

	/**
	 * Converts a query parameter to an array.
	 */
	private function parameterToArray(Parameter $parameter): array {
		return [$parameter->getName(), $parameter->getType(), $parameter->getValue()];
	}


	public function getParent(): string
    {
        return EntityType::class;
    }
}