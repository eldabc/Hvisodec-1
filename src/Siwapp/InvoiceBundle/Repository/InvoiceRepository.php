<?php

namespace Siwapp\InvoiceBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

use Siwapp\CoreBundle\Entity\Serie;
use Siwapp\CoreBundle\Repository\AbstractInvoiceRepository;
use Siwapp\InvoiceBundle\Entity\Invoice;

/**
 * InvoiceRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class InvoiceRepository extends AbstractInvoiceRepository
{
    protected function addPaginatedSearchSelects(QueryBuilder $qb)
    {
        parent::addPaginatedSearchSelects($qb);
        // For invoices we allow sorting by due.
        $qb->addSelect('i.gross_amount - i.paid_amount AS due_amount');

        return $qb;
    }
    
    public function searchReceipts(array $params){
        
        $qb = $this->getEntityManager()
        ->createQueryBuilder()
        ->select('i')
        ->from($this->getEntityName(), 'i')
        ->where('i.issue_date >= :date_from')
        ->andWhere('i.issue_date <= :date_to')
        ->andWhere('i.domicile = 1')
        ->setParameter('date_from', $params['date_from']->format('Y-m-d'))
        ->setParameter('date_to', $params['date_to']->format('Y-m-d'));
        
        return $qb->getQuery()->getResult();
        
    }
    
    public function updateInvoiceDue(){
        
        $fecha = new \DateTime('now');
        
        $qb = $this->getEntityManager()
        ->createQueryBuilder()
        ->update($this->getEntityName(), 'i')
        ->set('i.status', 3)
        ->where('i.due_date <= :due')
        ->andWhere('i.status != :borrador')
        ->andWhere('i.status != :cerrada')
        ->andWhere('i.status != :vencida')
        ->setParameter('due', $fecha->format('Y-m-d'))
        ->setParameter('borrador', Invoice::DRAFT)
        ->setParameter('cerrada', Invoice::CLOSED)
        ->setParameter('vencida', Invoice::OVERDUE)
        ->getQuery();
        
        return $qb->execute();
        
    }
}