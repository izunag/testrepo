<?php

class XMLRPClientWuBook {
  public $XMLRPCURL = "https://wubook.net/xrws/";
  public $user;
  public $password;
  public $provider_key;
  public $token;
  public $lcode;
  public $xmlrpcResponse;
  public $httpResponse;

  public function __construct($user, $pass, $pkey) {
    $this->user = $user;
    $this->password = $pass;
    $this->provider_key = $pkey;
    $this->error = false;
  }

// Authentication functions
// All is implemented
  
  public function acquire_token() {
    $params = [
      $this->user,
      $this->password,
      $this->provider_key, 
    ];
    $this->httpResponse = $this->send_request('acquire_token', $params);
    $this->decode_xmlrcp();
    $this->token = $this->xmlrpcResponse[1];
  }

  public function release_token() {  
    $this->httpResponse = $this->send_request('release_token', [$this->token]);
    $this->decode_xmlrcp();
  }

  public function is_token_valid() {  
    $this->httpResponse = $this->send_request('is_token_valid', [$this->token]);
    $this->decode_xmlrcp();
  }

  public function provider_info() {  
    $this->httpResponse = $this->send_request('provider_info', [$this->token]);
    $this->decode_xmlrcp();
  }
  
// Room functions

  public function fetch_rooms($lcode, $ancillary = 0) {
    $params = [
      $this->token,
      $lcode,
      $ancillary, 
    ];  
    $this->httpResponse = $this->send_request(__FUNCTION__, $params);
    $this->decode_xmlrcp();
  }

  public function fetch_rooms_values($lcode, $dfrom, $dto, $rooms = []) {
    $params = [
      $this->token,
      $lcode,
      $dfrom,
      $dto,
      $rooms, 
    ];  
    $this->httpResponse = $this->send_request(__FUNCTION__, $params);
    $this->decode_xmlrcp();
  }

  public function room_images($lcode, $rid) {
    $params = [
      $this->token,
      $lcode,
      $rid, 
    ];  
    $this->httpResponse = $this->send_request(__FUNCTION__, $params);
    $this->decode_xmlrcp();
  }

  public function get_pricing_plans($lcode) {
    $params = [
      $this->token,
      $lcode, 
    ];  
    $this->httpResponse = $this->send_request(__FUNCTION__, $params);
    $this->decode_xmlrcp();    
  }
  
// Restriction functions

  public function rplan_rplans($lcode) {
    $params = [
      $this->token,
      $lcode, 
    ];  
    $this->httpResponse = $this->send_request(__FUNCTION__, $params);
    $this->decode_xmlrcp();    
  }

  public function rplan_get_rplan_values($lcode, $dfrom, $dto, $rpids = []) {
    $params = [
      $this->token,
      $lcode,
      $dfrom, 
      $dto,
      $rpids, 
    ];  
    $this->httpResponse = $this->send_request(__FUNCTION__, $params);
    $this->decode_xmlrcp();    
  }
  
//  Corporate functions
  
  public function corporate_fetch_accounts($acode = '') {
    $params = [
      $this->token,
      $acode,
    ];  
    $this->httpResponse = $this->send_request(__FUNCTION__, $params);
    $this->decode_xmlrcp();    
  }
  
  public function corporate_get_providers_info($acodes = []) {
    $params = [
      $this->token,
      $acodes,
    ];  
    $this->httpResponse = $this->send_request(__FUNCTION__, $params);
    $this->decode_xmlrcp();    
  }
    
  public function corporate_fetchable_properties() {
    $params = [
      $this->token,
    ];  
    $this->httpResponse = $this->send_request(__FUNCTION__, $params);
    $this->decode_xmlrcp();    
  }
  
  //Renewing services
      
  //Request and decoding

  private function send_request($rpc, $params) {
    $postdata = xmlrpc_encode_request($rpc, $params);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $this->XMLRPCURL);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36');
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    $content = curl_exec($ch);
    $errno = curl_errno($ch);
    $error = curl_error($ch);
    if (0 === $errno) {
        return $content;
      } else {
        throw new Exception($error, $errno);
    }
  }

  private function decode_xmlrcp() {
    $this->xmlrpcResponse = xmlrpc_decode($this->httpResponse);
    if (is_array($this->xmlrpcResponse) && xmlrpc_is_fault($this->xmlrpcResponse)) {
      throw new Exception($this->xmlrpcResponse['faultString'], $this->xmlrpcResponse['faultCode']);
    }
  }
  
}