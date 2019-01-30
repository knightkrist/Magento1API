<?php

include 'ProductToOrderItem.php';

class AddProduct extends OrderGenerator
{
    public function _addProduct($requestData)
    {
        $request = new Varien_Object();
        $request->setData($requestData);

        $product = Mage::getModel('catalog/product')->load($request['product']);

        $cartCandidates = $product->getTypeInstance(true)
            ->prepareForCartAdvanced($request, $product);

        if (is_string($cartCandidates)) {
            throw new Exception($cartCandidates);
        }

        if (!is_array($cartCandidates)) {
            $cartCandidates = array($cartCandidates);
        }

        $parentItem = null;
        $errors = array();
        $items = array();
        foreach ($cartCandidates as $candidate) {

            $item = ProductToOrderItem::_productToOrderItem($candidate, $candidate->getCartQty());

            $items[] = $item;

            if (!$parentItem) {
                $parentItem = $item;
            }
            if ($parentItem && $candidate->getParentProductId()) {
                $item->setParentItem($parentItem);
            }

            $item->setQty($item->getQty() + $candidate->getCartQty());

            if ($item->getHasError()) {
                $message = $item->getMessage();
                if (!in_array($message, $errors)) { 
                    $errors[] = $message;
                }
            }
        }

        if (!empty($errors)) {
            Mage::throwException(implode("\n", $errors));
        }

        return $items;
    }

    
}

?>