<?php

define('itRECAPTCHA','recaptcha');
class uRecaptcha {
	static function Init() {
		utopia::AddInputType(itRECAPTCHA,array(__CLASS__,'Show'));
		modOpts::AddOption('recaptcha_public','Public Key','Recaptcha');
		modOpts::AddOption('recaptcha_private','Private Key','Recaptcha');
	}
	static function drawinput($fieldName,$inputType,$defaultValue='',$possibleValues=NULL,$attributes = NULL,$noSubmit = FALSE) {
		return self::Show();
	}
	static function Show() {
		if (isset($_SESSION['recaptcha_human_verified']) && $_SESSION['recaptcha_human_verified']) return '';
		$publickey = modOpts::GetOption('recaptcha_public');
		if (!$publickey) { return 'reCAPTCHA has not configured'; }
		uJavascript::LinkFile('https://www.google.com/recaptcha/api.js');
		$err = self::IsValid(); if ($err === true) $err = null;
		return '<div class="g-recaptcha" data-sitekey="' . $publickey . '"></div>';
	}
	static function IsValid() {
		if (isset($_SESSION['recaptcha_human_verified']) && $_SESSION['recaptcha_human_verified']) return true;
		if (!isset($_POST['g-recaptcha-response'])) return NULL;
		$privatekey = modOpts::GetOption('recaptcha_private');

		$ipAddress = $_SERVER['REMOTE_ADDR'];
		if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
			$ipAddress = array_pop(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
		}

		$ch = curl_init();
		$fields = array('secret'=>$privatekey,'response'=>$_POST['g-recaptcha-response'],'remoteip'=>$ipAddress);
		$query = http_build_query($fields);
		curl_setopt($ch,CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
		curl_setopt($ch,CURLOPT_POST, count($fields));
		curl_setopt($ch,CURLOPT_POSTFIELDS, $query);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		$result = curl_exec($ch);
		curl_close($ch);

		if ($result){
			$decode = json_decode($result,true);
			if ($decode['success']){
				$_SESSION['recaptcha_human_verified'] = true;
				return true;
			}
		}
		return 'Human Validation Failed';
	}
}

uEvents::AddCallback('AfterInit','uRecaptcha::Init');
