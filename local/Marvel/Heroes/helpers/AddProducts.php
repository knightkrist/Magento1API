<?php

include 'AddProduct.php';

class AddProducts{

    public function _addProducts(OrderModel $order, $products)
    {
        $subTotal = 0;

        $tempValue = $order->_subTotal;

        foreach ($products as $productRequest) {
            
            if ($productRequest['product'] == 'rand') {

                $productsCollection = Mage::getResourceModel('catalog/product_collection');

                $productsCollection->addFieldToFilter('type_id', 'simple');
                Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($productsCollection);

                $productsCollection->getSelect()
                    ->order('RAND()')
                    ->limit(rand($productRequest['min'], $productRequest['max']));

                foreach ($productsCollection as $product){
                    $productItems =
                    AddProduct::_addProduct(array(
                            'product' => $product->getId(),
                            'qty' => rand(1, 2)
                        ));
                }
            }
            else {
                $productItems = 
                AddProduct::_addProduct($productRequest);
            }
            foreach ($productItems as $item){
                $item->setStoreId($order->_storeId);
                $order->_order->addItem($item);
                $order->_subTotal += $item->getRowTotal();
            }

        }

    }
}
    
?>