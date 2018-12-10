<?php

class cLng 
{
/* 1:------------------------------method start here getLanguage 1------------------------------*/
static function lng($phrase,$lng)
 {
	$lnggnl = 
	array(
	'phoneexist' => array('This phone number is already registered.','.رقم الهاتف هذا مسجل بالفعل'),
	'emailexist' => array('This email is already registered.','عنوان البريد الإلكترونى هذا مسجل بالفعل'),
	'invalidPhone' => array('This phone number is inValid.','رقم الهاتف هذا غير صحيح'),
	'Welcomemsg' => array('Welcome to Mankoosha!','مرحبا بكم في منقوشه وا أكثر'),
	'not_valid_credential_email' => array('Email is incorrect.','.البريد الإلكتروني غير صحيح'),
	'not_valid_credential_phone' => array('Phone is incorrect.','رقم الهاتف غير صحيح'),
	'not_valid_credential_pass' => array('Password is incorrect.','كلمة المرور غير صحيحة'),
	'emailnotexist' => array('It is not a registered email.','هذا البريد الإلكتروني غير مسجل'),
	'same' => array('Old and new password are same.','كلمة المرور القديمة والجديدة هي نفسها'),
	'wrong' => array('Old password is wrong.','كلمة المرور القديمة خاطئة'),
	'promoCodeNotvalid' => array('Promo code not valid.','الرمز الترويجي غير صالح'),
	'tokeninvalid' => array('It is not a valid password_reset_token.','هذا ليس رمزًا صالحًا لإعادة تعيين كلمة المرور'),
	'codeinvalid' => array('It is not a valid code.','هذا ليس رمزًا صالحًا لإعادة تعيين كلمة المرور'),
	'wrongStatus' => array('Sorry, but you cannot cancel the order at this stage.','آسف، ولكن لا يمكنك إلغاء النظام في هذه المرحلة'),
	'wrongStatus2' => array('Sorry, the status is incorrect.','آسف، ولكن وضع خاطئ'),
	'not_ready' => array('Not updated due to incorrect Driver ID or Status.','لم يتم تحديثه بسبب معرف السائق الخاطئ أو حالته'),
	'alreadyexist' => array('Already exists.','موجود أصلا'),
	'not_completed' => array('Not yet completed the order.','لم تكتمل بعد'),
	'orderplaced' => array('Your order has been placed successfully. Now, you can track your order from the track order section.','تم تقديم طلبك بنجاح. يمكنك تتبع طلبك من قسم طلب المسار'),
	);
	
	return $lnggnl[$phrase][$lng];
 }
/* END-----------------------------END END END END-----------------------------END-*/
}

//return $result = LanMethods::getLanguage('phoneexist','1');

?>