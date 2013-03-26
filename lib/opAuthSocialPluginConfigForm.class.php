<?php
class opAuthSocialPluginConfigForm extends sfForm
{
  protected $configs = array(
    'cqc.jp.oneall_subdomain' => 'cqc.jp.oneall_subdomain',
    'cqc.jp.oneall_public_key' => 'cqc.jp.oneall_public_key',
    'cqc.jp.oneall_private_key' => 'cqc.jp.oneall_private_key'
  );
  public function configure()
  {
    $this->setWidgets(array(
      'cqc.jp.oneall_subdomain' => new sfWidgetFormInput(),
      'cqc.jp.oneall_public_key' => new sfWidgetFormInput(),
      'cqc.jp.oneall_private_key' => new sfWidgetFormInput(),
    ));
    $this->setValidators(array(
      'cqc.jp.oneall_subdomain' => new sfValidatorString(array(),array()),
      'cqc.jp.oneall_public_key' => new sfValidatorString(array(),array()),
      'cqc.jp.oneall_private_key' => new sfValidatorString(array(),array()),
    ));

    foreach($this->configs as $k => $v)
    {
      $config = Doctrine::getTable('SnsConfig')->retrieveByName($v);
      if($config)
      {
        $this->getWidgetSchema()->setDefault($k,$config->getValue());
      }
    }
    $this->getWidgetSchema()->setNameFormat('oneall[%s]');
  }
  public function save()
  {
    foreach($this->getValues() as $k => $v)
    {
      if(!isset($this->configs[$k]))
      {
        continue;
      }
      $config = Doctrine::getTable('SnsConfig')->retrieveByName($this->configs[$k]);
      if(!$config)
      {
        $config = new SnsConfig();
        $config->setName($this->configs[$k]);
      }
      $config->setValue($v);
      $config->save();
    }
  }
  public function validate($validator,$value,$arguments = array())
  {
    return $value;
  }
}
