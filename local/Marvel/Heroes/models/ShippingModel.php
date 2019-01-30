<?php

class ShippingModel
{
    public static $_shippingMethodCode = 'freeshipping';
    public static $_shippingMethod = 'freeshipping_freeshipping';
    public static $_shippingDescription = 'Free Shipping - Free';
    public static $_paymentMethod = 'checkmo';

    public function setPaymentMethod($methodName)
    {
        $this->_paymentMethod = $methodName;
    }
    
    public function setShippingMethod($methodName)
    {
        $this->_shippingMethod = $methodName;
    }
}

?>