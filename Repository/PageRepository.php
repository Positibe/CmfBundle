<?php
/**
 * This file is part of the PositibeLabs Projects.
 *
 * (c) Pedro Carlos Abreu <pcabreus@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Positibe\Bundle\CmfBundle\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Gedmo\Translatable\TranslatableListener;
use Positibe\Bundle\OrmContentBundle\Entity\Page;
use Positibe\Bundle\OrmContentBundle\Entity\Traits\PageRepositoryTrait;
use Positibe\Bundle\OrmMenuBundle\Entity\HasMenuRepositoryInterface;
use Positibe\Bundle\OrmRoutingBundle\Entity\HasRoutesRepositoryInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\RequestStack;


/**
 * Class PageRepository
 * @package Positibe\Bundle\CmfBundle\Repository
 *
 * @author Pedro Carlos Abreu <pcabreus@gmail.com>
 */
class PageRepository extends EntityRepository implements HasMenuRepositoryInterface, HasRoutesRepositoryInterface
{
    use PageRepositoryTrait;

    private $locale;

    /**
     * @param mixed $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    public function createNewByParentName($name)
    {
        /** @var Page $category */
        $category = $this->getQueryBuilder()->select('c')->from('PositibeOrmContentBundle:Page', 'c')
            ->where('c.name = :name')
            ->setParameter('name', $name)->getQuery()->getOneOrNullResult();

        /** @var Page $staticContent */
        $staticContent = parent::createNew();
        $staticContent->setParent($category);

        return $staticContent;
    }

    public function findOneByRoutes($route)
    {
        $qb = $this->createQueryBuilder('o')
            ->addSelect('seo', 'image', 'r')
            ->leftJoin('o.image', 'image')
            ->leftJoin('o.seoMetadata', 'seo')
            ->join('o.routes', 'r')
            ->where('r = :route')
            ->setParameter('route', $route);

        return $this->getQuery($qb)->getOneOrNullResult();
    }

    public function getQuery(QueryBuilder $qb)
    {
        $query = $qb->getQuery();

        if ($this->locale) {
            $query->setHint(
                TranslatableListener::HINT_TRANSLATABLE_LOCALE,
                $this->locale // take locale from session or request etc.
            );

            $query->setHint(
                TranslatableListener::HINT_FALLBACK,
                1 // fallback to default values in case if record is not translated
            );
        }

        $query->setHint(
            Query::HINT_CUSTOM_OUTPUT_WALKER,
            'Positibe\\Bundle\\CmfBundle\\Doctrine\\Query\\TranslationWalker'
        );
        return $query;
    }

    public function findContentByParent($parent, $count = 2, $sort = 'publishStartDate', $order = 'DESC')
    {
        $qb = $this->createQueryBuilder('o')
            ->addSelect('p', 'routes', 'image')
            ->leftJoin('o.routes', 'routes')
            ->leftJoin('o.image', 'image')
            ->innerJoin('o.parent', 'p');
        if (is_string($parent)) {
            $qb->where('p.name = :parent');
        } else {
            $qb->where('p = :parent');
        }
        $qb->setParameter('parent', $parent)
            ->setMaxResults($count)
            ->orderBy(sprintf('o.%s', $sort), $order);

        return $this->getQuery($qb)->getResult();
    }

    public function findOneContent($content)
    {
        $qb = $this->createQueryBuilder('o')
            ->addSelect('image', 'routes')
            ->where('o = :content')
            ->leftJoin('o.image', 'image')
            ->leftJoin('o.routes', 'routes')
            ->setParameter('content', $content);

        return $this->getQuery($qb)->getOneOrNullResult();
    }

    public function findOneByName($name)
    {
        $qb = $this->createQueryBuilder('o')
            ->addSelect('image', 'routes')
            ->where('o.name = :name')
            ->leftJoin('o.image', 'image')
            ->leftJoin('o.routes', 'routes')
            ->setParameter('name', $name);

        return $this->getQuery($qb)->getOneOrNullResult();
    }
} 