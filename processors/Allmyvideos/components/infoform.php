<?php

class SPVIDEOLITE_PRO_ALLMYVIDEOS_CMP_Infoform extends Form
{
  /**
   * Class constructor
   *
   */
  public function __construct($required = false)
  {
    parent::__construct('AllmyvideosVideoInfoForm');

    $language = OW::getLanguage();

    // title Field
    $titleField = new TextField('title');
    $titleField->setRequired($required);
    $this->addElement($titleField->setLabel($language->text('video', 'title')));

    // description Field
    $descField = new WysiwygTextarea('description');
    $this->addElement($descField->setLabel($language->text('video', 'description')));

    $tagsField = new TagsInputField('tags');
    $this->addElement($tagsField->setLabel($language->text('video', 'tags')));

    $tokenField = new HiddenField('token');
    $this->addElement($tokenField);

    $filenameField = new HiddenField('filename');
    $this->addElement($filenameField);

    $protocolField = new HiddenField('protocol');
    $this->addElement($protocolField);

    $submit = new Submit('save');
    $submit->setValue($language->text('spvideo', 'btn_save'));
    $this->addElement($submit);
  }

  /**
   * Adds video clip
   *
   * @return boolean
   */
  public function process()
  {
    $values = $this->getValues();
    $clipService = VIDEO_BOL_ClipService::getInstance();

    $clip = new VIDEO_BOL_Clip();
    $clip->title = htmlspecialchars($values['title']);
    $description = UTIL_HtmlTag::stripJs($values['description']);
    $description = UTIL_HtmlTag::stripTags($description, array('frame', 'style'), array(), true);
    $description = nl2br($description, true);
    $clip->description = $description;
    $clip->userId = OW::getUser()->getId();
    $clip->code = '<iframe src="'.(OW::getRouter()->getBaseUrl().'spvideo/proxy/Allmyvideos/pending/').$values['token'].'" width="540" height="315" frameborder="0"></iframe>';

    $privacy = OW::getEventManager()->call(
        'plugin.privacy.get_privacy',
        array('ownerId' => $clip->userId, 'action' => 'video_view_video')
    );
                
    $clip->provider = 'allmyvideos';
    $clip->addDatetime = time();
    $clip->status = 'approved';
    $clip->privacy = mb_strlen($privacy) ? $privacy : 'everybody';

    $eventParams = array('pluginKey' => 'video', 'action' => 'add_video');

    if ( OW::getEventManager()->call('usercredits.check_balance', $eventParams) === true )
    {
        OW::getEventManager()->call('usercredits.track_action', $eventParams);
    }
    
    if ( $clipService->addClip($clip) )
    {
      SPVIDEOLITE_PRO_ALLMYVIDEOS_CLASS_Processing::processTemporaryUpload($values['token'],$values['filename']);

      BOL_TagService::getInstance()->updateEntityTags($clip->id, 'video', $values['tags']);

      return array('result' => true, 'id' => $clip->id);
    }

    return false;
  }
}