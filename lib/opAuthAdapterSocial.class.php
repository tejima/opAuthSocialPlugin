<?php

/**
 */

/**
 * opAuthAdapterWithTwitter will handle authentication for OpenPNE by Twitter OAuth
 *
 * @package    OpenPNE
 * @subpackage user
 * @author     Mamoru Tejima <tejima@tejimaya.com>
 */
class opAuthAdapterSocial extends opAuthAdapter
{
    //$urlCallback = null,
  protected
    $authModuleName = 'Social';

  public function configure()
  {
    $this->urlCallback = $this->getRequest()->getUri();
  }

  public function getTokenURL(){
    return sfConfig::get("op_base_url",null)."/member/login/authMode/Social";
  }
  public function authenticate()
  {
    $result = parent::authenticate();

    //Your Site Settings
    $site_subdomain =  opConfig::get('cqc.jp.oneall_subdomain');
    $site_public_key = opConfig::get('cqc.jp.oneall_public_key');
    $site_private_key = opConfig::get('cqc.jp.oneall_private_key');

    //API Access Domain
    $site_domain = $site_subdomain.'.api.oneall.com';

    //Connection Resource
    $resource_uri = 'https://'.$site_domain.'/connections/'.$_POST['connection_token'].".json";

    //Setup connection
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $resource_uri);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_USERPWD, $site_public_key . ":" . $site_private_key);
    curl_setopt($curl, CURLOPT_TIMEOUT, 15);
    curl_setopt($curl, CURLOPT_VERBOSE, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($curl, CURLOPT_FAILONERROR, 0);

    //Send request
    $result_json = curl_exec($curl);
 
    //Error
    if ($result_json === false)
    {
      //You may want to implement your custom error handling here
      echo 'Curl error: ' . curl_error($curl). '<br />';
      echo 'Curl info: ' . curl_getinfo($curl). '<br />';
      curl_close($curl);
      throw new Exception("Exception: auth server error");
    }
    //Close connection
    curl_close($curl);
 
    //Decode
    $json = json_decode ($result_json);
 
    //Extract data
    $data = $json->response->result->data;
 
    //Check for plugin
    if ($data->plugin->key != 'social_login' || $data->plugin->data->status != 'success')
    {
      throw new Exception("Exception: auth server error");  
    }
    //The token of the user that signed in/up using his social network account
    $user_token = $data->user->user_token;

    // 1] Check if you have a userID for this token in your database
    $user_id = $this->GetUserIdForUserToken($user_token);

    // 2.1] If the userID is empty then this is the first time that this user signs in
    if ($user_id === null)
    {
      // 2.1.1] Create a new account (optionally display a form to collect  more data about the user).
      // Insert you proprietary account creation code here.
      //echo "create user";
      // 2.1.2] Attach the user_token to the userID of the created account.
      $member = Doctrine::getTable('Member')->createPre();
      $member->setName("NoName");
      $member->setIsActive(true);
      $member->save();
      $user_id = $member->id;
      $this->LinkUserTokenToUserId ($user_token, $user_id);
    }
    // 2.2] If you DO have an userID for the user_token then this is a returning visitor
    else
    {
      // 2.2.1] The account already exists
      //echo "account exists";
      $member_id = $user_id;
      var_dump($user_id);
      $member = Doctrine::getTable('Member')->find($member_id);
      echo "MEMBER";
      print_r($member->id);
    }
    $result = $member->getId();

    $uri = sfContext::getInstance()->getUser()->getAttribute('next_uri');
    if($uri)
    {
      sfContext::getInstance()->getUser()->setAttribute('next_uri', null);
      $this->getAuthForm()->setNextUri($uri);
    }
    return $result;
  }

  public function registerData($memberId, $form)
  {
    $member = Doctrine::getTable('Member')->find($memberId);
    if (!$member)
    {
      return false;
    }

    $member->setIsActive(true);
    return $member->save();
  }


  public function isRegisterBegin($member_id = null)
  {
    opActivateBehavior::disable();
    $member = Doctrine::getTable('Member')->find((int)$member_id);
    opActivateBehavior::enable();

    if (!$member || $member->getIsActive())
    {
      return false;
    }

    return true;
  }

  public function isRegisterFinish($member_id = null)
  {
    return false;
  }
  private function GetUserIdForUserToken($user_token){
   // $result = Doctrine_Query::create()->select()->from('MemberConfig mc')->where("name = 'slavepne'")->andWhere("value = ?","oneall:".$user_token)->execute();
    $mc = Doctrine::getTable("MemberConfig")->findOneByNameAndValue('oneall',$user_token);
    if($mc){
      return $mc->member_id;
    }else{
      return null;
    }
  }
  private function LinkUserTokenToUserId($user_token,$user_id){
    $m = Doctrine::getTable("Member")->find($user_id);
    //echo $user_id;
    //echo $m->name;
    if($m){
      $m->setConfig("oneall",$user_token);    
      sfContext::getInstance()->getLogger()->debug("user_token:" . $user_token);
    }else{
      //throw new Exception("Exception: Member not found while link.");
    }
  }


}
