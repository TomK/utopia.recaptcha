<?php

include('lib/recaptchalib.php');

define('itRECAPTCHA','recaptcha');
class uRecaptcha {
	static function Init() {
		utopia::AddInputType(itRECAPTCHA,array(__CLASS__,'drawinput'));
		modOpts::AddOption('recaptcha_public','Public Key','Recaptcha');
		modOpts::AddOption('recaptcha_private','Private Key','Recaptcha');
	}
	static function drawinput($fieldName,$inputType,$defaultValue='',$possibleValues=NULL,$attributes = NULL,$noSubmit = FALSE) {
		return self::Show();
	}
	static function Show() {
		$publickey = modOpts::GetOption('recaptcha_public');
		$err = self::IsValid(); if ($err === true) $err = null;
		return recaptcha_get_html($publickey,$err);
	}
	static function IsValid() {
		if (!isset($_POST["recaptcha_challenge_field"]) || !isset($_POST["recaptcha_response_field"])) return NULL;
		$privatekey = modOpts::GetOption('recaptcha_private');
		$resp = recaptcha_check_answer($privatekey, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
		return $resp->is_valid ? true : $resp->error;
	}
}

uEvents::AddCallback('AfterInit','uRecaptcha::Init');