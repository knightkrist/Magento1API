<?php

class ShippingInformation
{
    
    public function setShippingInformation(OrderModel $order, $shippingInformation)
    {
        $order->_order
            ->setShippingDescription($shippingInformation['title'] . ' - ' . $shippingInformation['shipping_name'])
            ->setShippingMethod($order->_shippingMethod)
            ->setBaseShippingAmount(0)
            ->setBaseShippinTaxAmount(0)
            ->setShippinTaxAmount(0)
            ->setShippinInclTax(0)
            ->setBaseShippinInclTax(0)
            ->setShippingAmount(0);

        return $order;
    }

    public function getShippingInformationByCode($shippingCode)
    {
        $shippingInformation = [];
        $shippingmethods = Mage::getSingleton('shipping/config')->getActiveCarriers();

        foreach ($shippingmethods as $code => $carrier) {

            if ($shippingCode == $code) {
                $shippingInformation = [
                    'title' => Mage::getStoreConfig('carriers/' . $code . '/title'),
                    'shipping_name' => Mage::getStoreConfig('carriers/' . $code . '/name'),
                    'ship_error_msg' => Mage::getStoreConfig('carriers/' . $code . '/specificerrmsg'),
                ];
            }
        }

        return $shippingInformation ?: false;
    }
}

?>