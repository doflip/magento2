<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Upsell products admin grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Upsell extends Mage_Adminhtml_Block_Widget_Grid
{

    /**
     * Set grid params
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('up_sell_product_grid');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
        if ($this->getProduct() && $this->getProduct()->getId()) {
            $this->setDefaultFilter(array('in_products'=>1));
        }
        if ($this->isReadonly()) {
            $this->setFilterVisibility(false);
        }
    }

    /**
     * Retirve currently edited product model
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return Mage::registry('current_product');
    }

    /**
     * Add filter
     *
     * @param object $column
     * @return Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Upsell
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in'=>$productIds));
            } else {
                if($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', array('nin'=>$productIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Checks when this block is readonly
     *
     * @return boolean
     */
    public function isReadonly()
    {
        return $this->getProduct() && $this->getProduct()->getUpsellReadonly();
    }

    /**
     * Prepare collection
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('Mage_Catalog_Model_Product_Link')->useUpSellLinks()
            ->getProductCollection()
            ->setProduct($this->getProduct())
            ->addAttributeToSelect('*');

        if ($this->isReadonly()) {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = array(0);
            }
            $collection->addFieldToFilter('entity_id', array('in'=>$productIds));
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Add columns to grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        if (!$this->getProduct()->getUpsellReadonly()) {
            $this->addColumn('in_products', array(
                'type'      => 'checkbox',
                'name'      => 'in_products',
                'values'    => $this->_getSelectedProducts(),
                'align'     => 'center',
                'index'     => 'entity_id',
                'header_css_class'  => 'col-select',
                'column_css_class'  => 'col-select'
            ));
        }

        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('Mage_Catalog_Helper_Data')->__('ID'),
            'sortable'  => true,
            'index'     => 'entity_id',
            'header_css_class'  => 'col-id',
            'column_css_class'  => 'col-id'
        ));
        $this->addColumn('name', array(
            'header'    => Mage::helper('Mage_Catalog_Helper_Data')->__('Name'),
            'index'     => 'name',
            'header_css_class'  => 'col-name',
            'column_css_class'  => 'col-name'
        ));

        $this->addColumn('type', array(
            'header'    => Mage::helper('Mage_Catalog_Helper_Data')->__('Type'),
            'index'     => 'type_id',
            'type'      => 'options',
            'options'   => Mage::getSingleton('Mage_Catalog_Model_Product_Type')->getOptionArray(),
            'header_css_class'  => 'col-type',
            'column_css_class'  => 'col-type'
        ));

        $sets = Mage::getResourceModel('Mage_Eav_Model_Resource_Entity_Attribute_Set_Collection')
            ->setEntityTypeFilter(Mage::getModel('Mage_Catalog_Model_Product')->getResource()->getTypeId())
            ->load()
            ->toOptionHash();

        $this->addColumn('set_name', array(
            'header'    => Mage::helper('Mage_Catalog_Helper_Data')->__('Attribute Set'),
            'index'     => 'attribute_set_id',
            'type'      => 'options',
            'options'   => $sets,
            'header_css_class'  => 'col-attr-name',
            'column_css_class'  => 'col-attr-name'
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('Mage_Catalog_Helper_Data')->__('Status'),
            'index'     => 'status',
            'type'      => 'options',
            'options'   => Mage::getSingleton('Mage_Catalog_Model_Product_Status')->getOptionArray(),
            'header_css_class'  => 'col-status',
            'column_css_class'  => 'col-status'
        ));

        $this->addColumn('visibility', array(
            'header'    => Mage::helper('Mage_Catalog_Helper_Data')->__('Visibility'),
            'index'     => 'visibility',
            'type'      => 'options',
            'options'   => Mage::getSingleton('Mage_Catalog_Model_Product_Visibility')->getOptionArray(),
            'header_css_class'  => 'col-visibility',
            'column_css_class'  => 'col-visibility'
        ));

        $this->addColumn('sku', array(
            'header'    => Mage::helper('Mage_Catalog_Helper_Data')->__('SKU'),
            'index'     => 'sku',
            'header_css_class'  => 'col-sku',
            'column_css_class'  => 'col-sku'
        ));

        $this->addColumn('price', array(
            'header'        => Mage::helper('Mage_Catalog_Helper_Data')->__('Price'),
            'type'          => 'currency',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'index'         => 'price',
            'header_css_class'  => 'col-price',
            'column_css_class'  => 'col-price'
        ));

        $this->addColumn('position', array(
            'header'            => Mage::helper('Mage_Catalog_Helper_Data')->__('Position'),
            'name'              => 'position',
            'type'              => 'number',
            'validate_class'    => 'validate-number',
            'index'             => 'position',
            'editable'          => !$this->getProduct()->getUpsellReadonly(),
            'edit_only'         => !$this->getProduct()->getId(),
            'header_css_class'  => 'col-position',
            'column_css_class'  => 'col-position'
        ));

        return parent::_prepareColumns();
    }

    /**
     * Rerieve grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->_getData('grid_url') ? $this->_getData('grid_url') : $this->getUrl('*/*/upsellGrid', array('_current'=>true));
    }

    /**
     * Retrieve selected upsell products
     *
     * @return array
     */
    protected function _getSelectedProducts()
    {
        $products = $this->getProductsUpsell();
        if (!is_array($products)) {
            $products = array_keys($this->getSelectedUpsellProducts());
        }
        return $products;
    }

    /**
     * Retrieve upsell products
     *
     * @return array
     */
    public function getSelectedUpsellProducts()
    {
        $products = array();
        foreach (Mage::registry('current_product')->getUpSellProducts() as $product) {
            $products[$product->getId()] = array('position' => $product->getPosition());
        }
        return $products;
    }

}
