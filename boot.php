<?php

/**
 * @var rex_addon $this
 */
 
$addon = rex_addon::get('flexshop');

if (rex::isFrontend()) {
    rex_extension::register('OUTPUT_FILTER', static function (rex_extension_point $ep) use ($addon) {
        
		$ep->setSubject(str_ireplace(
				['</head>'],
				['<link rel="stylesheet" href="'. $addon->getAssetsUrl('flexshop.css') .'"></head>'],
				$ep->getSubject())
		);
    });
	
    rex_extension::register('OUTPUT_FILTER', static function (rex_extension_point $ep) use ($addon) {
                
		$ep->setSubject(str_ireplace(
				['</body>'],
				[rex_flexshop_modals::getModal('addsuccess').'<script src="'. $addon->getAssetsUrl('flexshop.js') .'"></script></body>'],
				$ep->getSubject())
		);
    });
}
 
rex_yform_manager_dataset::setModelClass('rex_flexshop_object', rex_flexshop_object::class);
rex_yform_manager_dataset::setModelClass('rex_flexshop_category', rex_flexshop_category::class);
rex_yform_manager_dataset::setModelClass('rex_flexshop_country', rex_flexshop_country::class);

if (!rex::isBackend()) {
	rex_extension::register('OUTPUT_FILTER', function(rex_extension_point $ep) {
		
		$content = $ep->getSubject();

        $rex_flexshop = new rex_flexshop();
		
		if (!is_null(rex_article::getCurrent())) {
			preg_match_all("/REX_FLEXSHOP\[category=(.*?)]/", $content, $matches, PREG_SET_ORDER);
			
			foreach($matches as $match){
				$content = str_replace($match[0], rex_flexshop::getCategory($match[1]), $content);
			}

			preg_match_all("/REX_FLEXSHOP\[object=(.*?)]/", $content, $matches, PREG_SET_ORDER);

			foreach($matches as $match){
				$content = str_replace($match[0], rex_flexshop::get($match[1]), $content);
			}
			
			preg_match_all("/REX_FLEXSHOP\[cart]/", $content, $matches, PREG_SET_ORDER);
			
			foreach($matches as $match){
				$content = str_replace($match[0], $rex_flexshop->getCartOutput(), $content);
			}
			
			preg_match_all("/REX_FLEXSHOP\[cart=light]/", $content, $matches, PREG_SET_ORDER);
			
			foreach($matches as $match){
				$content = str_replace($match[0], rex_flexshop::getCartLight(), $content);
			}
		}
		
		$ep->setSubject($content);
	});
}

if (rex::isBackend())
{
	rex_extension::register("YFORM_DATA_UPDATED", function( $ep ) {

		if ($ep->getParam("table")->getTableName()=="rex_flexshop_order"){
			$list = $ep->getSubject();
			$oldData = $ep->getParam('old_data');
			$newData = $list->objparams['value_pool']['sql'];
			
			if($oldData['state'] !== "sent" && $newData['state'] === "sent"){
				$template_name = 'flexshop_user_order_sent';
				$etpl = rex_yform_email_template::getTemplate($template_name);
				$etpl = rex_yform_email_template::replaceVars($etpl, $newData);
				$etpl['mail_to'] = $newData['email'];
				$etpl['mail_to_name'] = $newData['firstname'].' '.$newData['lastname'];
				rex_yform_email_template::sendMail($etpl, $template_name);
			}
		}
	});
	
	
}