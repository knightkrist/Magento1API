<?php

include 'ShippingInformation.php'; 

include 'AddProducts.php';
include 'ApplyDiscount.php';

include 'CustomerGenerator.php';

include (dirname(dirname(__FILE__)) . '\models\OrderModel.php');
include (dirname(dirname(__FILE__)) . '\models\CustomerModel.php');
include (dirname(dirname(__FILE__)) . '\models\ShippingModel.php');

class OrderGenerator
{

    public function setCustomer($customer)
    {
        if ($customer instanceof Mage_Customer_Model_Customer){
            CustomerModel::$_customer = $customer;
        }
        if (is_numeric($customer)){
            CustomerModel::$_customer = Mage::getModel('customer/customer')->load($customer);
        }
        else if ($customer === CustomerModel::CUSTOMER_RANDOM){
            $customers = Mage::getResourceModel('customer/customer_collection');

            $customers
                ->getSelect()
                ->limit(1)
                ->order('RAND()');

            $id = $customers->getFirstItem()->getId();
            
            CustomerModel::$_customer = Mage::getModel('customer/customer')->load($id);
        }
    }
    
    public function createOrder($orderData)
    {
        
        $orderModel = new OrderModel(); 
        
        $customerGenerator = new CustomerGenerator();
                
        $customer = $customerGenerator->createCustomer($orderData);

        $this->setCustomer($customer);

        $products = $orderData['products'];
        $couponCode = $orderData['discountcode'];

        $transaction = Mage::getModel('core/resource_transaction');
        $orderModel->_storeId = CustomerModel::$_customer->getStoreId();
        $reservedOrderId = Mage::getSingleton('eav/config')
            ->getEntityType('order')
            ->fetchNewIncrementId($orderModel->_storeId);

        $currencyCode  = Mage::app()->getBaseCurrencyCode();
        $orderModel->_order = Mage::getModel('sales/order')
            ->setIncrementId($reservedOrderId)
            ->setStoreId($orderModel->_storeId)
            ->setQuoteId(0)
            ->setGlobalCurrencyCode($currencyCode)
            ->setBaseCurrencyCode($currencyCode)
            ->setStoreCurrencyCode($currencyCode)
            ->setOrderCurrencyCode($currencyCode);

        $orderModel->_order->setCustomerEmail(CustomerModel::$_customer->getEmail())
            ->setCustomerFirstname(CustomerModel::$_customer->getFirstname())
            ->setCustomerLastname(CustomerModel::$_customer->getLastname())
            ->setCustomerGroupId(CustomerModel::$_customer->getGroupId())
            ->setCustomerIsGuest(0)
            ->setCustomer(CustomerModel::$_customer);

        $billing = CustomerModel::$_customer->getDefaultBillingAddress();
        $billingAddress = Mage::getModel('sales/order_address')
            ->setStoreId($orderModel->_storeId)
            ->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_BILLING)
            ->setCustomerId(CustomerModel::$_customer->getId())
            ->setCustomerAddressId(CustomerModel::$_customer->getDefaultBilling())
            ->setCustomerAddress_id($billing->getEntityId())
            ->setPrefix($billing->getPrefix())
            ->setFirstname($billing->getFirstname())
            ->setMiddlename($billing->getMiddlename())
            ->setLastname($billing->getLastname())
            ->setSuffix($billing->getSuffix())
            ->setCompany($billing->getCompany())
            ->setStreet($billing->getStreet())
            ->setCity($billing->getCity())
            ->setCountry_id($billing->getCountryId())
            ->setRegion($billing->getRegion())
            ->setRegion_id($billing->getRegionId())
            ->setPostcode($billing->getPostcode())
            ->setTelephone($billing->getTelephone())
            ->setFax($billing->getFax())
            ->setVatId($billing->getVatId());
        $orderModel->_order->setBillingAddress($billingAddress);

        $shipping = CustomerModel::$_customer->getDefaultShippingAddress();
        $shippingAddress = Mage::getModel('sales/order_address')
            ->setStoreId($orderModel->_storeId)
            ->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_SHIPPING)
            ->setCustomerId(CustomerModel::$_customer->getId())
            ->setCustomerAddressId(CustomerModel::$_customer->getDefaultShipping())
            ->setCustomer_address_id($shipping->getEntityId())
            ->setPrefix($shipping->getPrefix())
            ->setFirstname($shipping->getFirstname())
            ->setMiddlename($shipping->getMiddlename())
            ->setLastname($shipping->getLastname())
            ->setSuffix($shipping->getSuffix())
            ->setCompany($shipping->getCompany())
            ->setStreet($shipping->getStreet())
            ->setCity($shipping->getCity())
            ->setCountry_id($shipping->getCountryId())
            ->setRegion($shipping->getRegion())
            ->setRegion_id($shipping->getRegionId())
            ->setPostcode($shipping->getPostcode())
            ->setTelephone($shipping->getTelephone())
            ->setFax($shipping->getFax())
            ->setVatId($billing->getVatId());

        $orderModel->_order->setShippingAddress($shippingAddress);

        if ($shippingInformation = ShippingInformation::getShippingInformationByCode(ShippingModel::$_shippingMethodCode)) {
            $shippingInformation['method'] = ShippingModel::$_shippingMethod;
            ShippingInformation::setShippingInformation($orderModel, $shippingInformation);
        }

        $orderPayment = Mage::getModel('sales/order_payment')
            ->setStoreId($orderModel->_storeId)
            ->setCustomerPaymentId(0)
            ->setMethod(ShippingModel::$_paymentMethod)
            ->setPoNumber(' – ');

        $orderModel->_order->setPayment($orderPayment);

        AddProducts::_addProducts($orderModel, $products);

        $orderModel->_order->setSubtotal($orderModel->_subTotal);

        $orderModel->_subTotal = ApplyDiscount::_applyDiscountCode($orderModel, $couponCode);

        $orderModel->_order->setBaseSubtotal($orderModel->_subTotal)
            ->setGrandTotal($orderModel->_subTotal)
            ->setBaseGrandTotal($orderModel->_subTotal);

        $transaction->addObject($orderModel->_order);
        $transaction->addCommitCallback(array($orderModel->_order, 'place'));
        $transaction->addCommitCallback(array($orderModel->_order, 'save'));
        $transaction->save();        
    }

}
