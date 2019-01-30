<?php

class ApplyDiscount{

    public function _applyDiscountCode($order, $couponCode)
    {

        $oCoupon = Mage::getModel('salesrule/coupon')->load(trim($couponCode), 'code');
        $oRule = Mage::getModel('salesrule/rule')->load($oCoupon->getRuleId());

        if($oRule->getRuleId() && $oRule->getRuleId() > 0){
            $tempSubTotal = $order->_subTotal;

            if($oRule['simple_action'] == 'by_percent'){

                $calculatedValue = ($tempSubTotal * $oRule->getDiscountAmount()) / 100;

                $order->_subTotal = $tempSubTotal - $calculatedValue;

                $order->_order 
                        ->setDiscountAmount($calculatedValue)
                        ->setBaseDiscountAmount($calculatedValue)
                        ->setDiscountDescription($couponCode);

            } else if($oRule['simple_action'] == 'by_fixed' || $oRule['simple_action'] == 'cart_fixed'){

                $order->_subTotal = $tempSubTotal - $oRule->getDiscountAmount();
                $order->_order
                        ->setDiscountAmount($oRule->getDiscountAmount())
                        ->setBaseDiscountAmount($oRule->getDiscountAmount())
                        ->setDiscountDescription($couponCode);
            } else {
                $order->_subTotal = $tempSubTotal;
            }

        }

        return $order->_subTotal;
    }
}
?>