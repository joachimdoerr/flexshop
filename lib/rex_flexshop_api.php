<?php

class rex_api_flexshop extends rex_api_function
{
    protected $published = true;

    function execute()
    {
		rex_login::startSession();
		
		$rex_flexshop_cart = new rex_flexshop_cart();
		$rex_flexshop_cart_light = new rex_flexshop_cart_light();

        // Parameter abrufen und auswerten
        $func = rex_request('func', 'string', '');
        $id = rex_request('id', 'string', '');

        $content = '';
        switch ($func) {
            case 'add':
                $content = $rex_flexshop_cart->addObject($id);
                break;
            case 'remove':
                $content = $rex_flexshop_cart->removeObject($id);
                break;
            case 'get_quantity':
                $content = $rex_flexshop_cart_light->getCountObjects();
                break;
        }

        if (!$content) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json; charset=UTF-8');
            $result = ['errorcode' => 1, 'message' => 'Object not found'];
            exit(json_encode($result));
        }

        // Inhalt ausgeben
        header('Content-Type: text/html; charset=UTF-8');
        echo $content;
        exit;
    }
}