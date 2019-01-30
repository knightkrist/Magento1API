<?php
 
 define('__ROOT__', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))); 
 require_once(__ROOT__.'\Mage.php'); 
 umask(0);
 Mage::app('default');

 require_once(__ROOT__.'\code\local\Marvel\Heroes\helpers\OrderGenerator.php'); 

class Marvel_Heroes_IndexController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
        header('Content-type: application/json');

        $jsonData = json_decode(file_get_contents('php://input'),true);

        $orderGenerator = new OrderGenerator();

        $orderGenerator->createOrder($jsonData);  
    }

}
?>