<?php

class CustomerGenerator
{

    protected $_defaultData;

    protected $_resource;

    protected $_adapter;

    public function __construct()
    {
        $this->_resource = Mage::getResourceSingleton('core/resource');
        $this->_adapter = $this->_resource->getReadConnection();
    }

    public function createCustomer($data = array())
    {

        $customer = Mage::getModel('customer/customer');

        $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
        $customer->loadByEmail($data['account']['email']);

        if(!$customer->getId())
        {        

            $customer->setData($data['account']);

            foreach (array_keys($data['address']) as $index) {
                $address = Mage::getModel('customer/address');

                $addressData = array_merge($data['account'], $data['address'][$index]);

                $isDefaultBilling = isset($data['account']['default_billing'])
                    && $data['account']['default_billing'] == $index;
                $address->setIsDefaultBilling($isDefaultBilling);
                $isDefaultShipping = isset($data['account']['default_shipping'])
                    && $data['account']['default_shipping'] == $index;
                $address->setIsDefaultShipping($isDefaultShipping);

                $address->addData($addressData);

                $address->setPostIndex($index);

                $customer->addAddress($address);
            }

            if (isset($data['account']['default_billing'])) {
                $customer->setData('default_billing', $data['account']['default_billing']);
            }
            if (isset($data['account']['default_shipping'])) {
                $customer->setData('default_shipping', $data['account']['default_shipping']);
            }
            if (isset($data['account']['confirmation'])) {
                $customer->setData('confirmation', $data['account']['confirmation']);
            }

            if (isset($data['account']['sendemail_store_id'])) {
                $customer->setSendemailStoreId($data['account']['sendemail_store_id']);
            }

            $customer
                ->setPassword($data['account']['password'])
                ->setForceConfirmed(true)
                ->save()
                ->cleanAllAddresses();

        }

        return $customer;
    }

}