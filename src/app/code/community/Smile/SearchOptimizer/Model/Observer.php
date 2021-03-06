<?php
/**
 * Search optimizer observer
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile Searchandising Suite to newer
 * versions in the future.
 *
 * This work is a fork of Johann Reinke <johann@bubblecode.net> previous module
 * available at https://github.com/jreinke/magento-elasticsearch
 *
 * @category  Smile
 * @package   Smile_SearchOptimizer
 * @author    Aurelien FOUCRET <aurelien.foucret@smile.fr>
 * @copyright 2013 Smile
 * @license   Apache License Version 2.0
 */
class Smile_SearchOptimizer_Model_Observer
{
    /**
     * Append optimize to queries.
     *
     * @param Varien_Event_Observer $observer Event to observe.
     *
     * @return Smile_SearchOptimizer_Model_Observer Self reference.
     */
    public function reindexPercolators(Varien_Event_Observer $observer)
    {
        $indexer = Mage::getModel('smile_searchoptimizer/indexer_percolator');
        $indexer->reindexAll();
        return $this;
    }

    /**
     * Append optimize to queries.
     *
     * @param Varien_Event_Observer $observer Event to observe.
     *
     * @return Smile_SearchOptimizer_Model_Observer Self reference.
     */
    public function addOptimizers(Varien_Event_Observer $observer)
    {
        $data = $observer->getQueryData();
        $queryType = $data->getQueryType();
        $query = $data->getQuery();

        if (!isset($query['search_type']) || $query['search_type'] != 'count') {
            $optimizers = Mage::getResourceModel('smile_searchoptimizer/optimizer_collection')
                ->addIsActiveFilter()
                ->addQueryTypeFilter($queryType);

            if ($storeId = $data->getStoreId()) {
                $optimizers->addStoreFilter($storeId);
            } else {
                $optimizers->addStoreFilter(Mage::app()->getStore());
            }

            foreach ($optimizers as $currentOptimizer) {
                $query = $currentOptimizer->applyOptimizer($query);
            }

            $data->setQuery($query);
        }
        return $this;
    }

}