<?php

/**
 *
 */
class SPVIDEOLITE_CTRL_Admin extends ADMIN_CTRL_Abstract {

	private $menu = null;

	function __construct() {
		$this->menu = $this->getMenu();
		$this->addComponent( 'menu', $this->menu );
		parent::__construct();
	}

	function setPageHeading( $heading ) {
		$heading = 'SPVIDEO LITE :: ' . $heading;

		return parent::setPageHeading( $heading );
	}

	function index() {
    $language = OW::getLanguage();
    $this->setPageHeading( 'Tweaks' );

    OW::getDocument()->addStyleSheet( SPVIDEOLITE_BOL_Service::getCssUrl('toggles-light') );
    OW::getDocument()->addScript( SPVIDEOLITE_BOL_Service::getJsUrl('jquery.toggles.min') );

    OW::getDocument()->addOnloadScript("
      $('.tweaksForm input[type=checkbox]').each(function(index,obj){
        var togglerId = $(obj).attr('id')+'_toggler';
        $(obj).parent().append('<div class=\"toggle-light\" id=\"'+togglerId+'\"></div>');
        $('#'+togglerId).toggles({
          drag: true,
          width: 55,
          text: {
            on: '".$language->text( 'spvideolite', 'chk_on' )."',
            off: '".$language->text( 'spvideolite', 'chk_off' )."'
          },
          on: $(obj).is(':checked'),
          checkbox: $(obj)
        });
        $(obj).hide();
        $('#'+togglerId).on('toggle',function(e,active){
          var configKey = 'tweaks.' + $(this).attr('id').replace('_toggler','');
          var postData = { key : configKey, value : active };
          if (active)
            postData.value = 1;
          else
            postData.value = 0;
          $.post(
            '".OW::getRouter()->urlForRoute('spvideolite.admin_saveconfig')."',
            postData,
            function( data ) {
            },
            'text'
          );
        });
      });
    ");
    $tweaks = SPVIDEOLITE_BOL_Configs::getInstance()->searchKey('#^tweaks\..+?$#im');
    $tweaksConfig = array();
    foreach ($tweaks as $tweak) {
      $tweaksConfig[substr($tweak, 7)] = SPVIDEOLITE_BOL_Configs::getInstance()->get($tweak);
    }
    $this->assign('tweaks',$tweaksConfig);
	}

	function getMenu() {
		$language = OW::getLanguage();

		$menu = new BASE_CMP_ContentMenu();
		$menuItems = array();

		$item = new BASE_MenuItem();
		$item->setLabel( $language->text( 'spvideolite', 'adm_menu_tweaks' ) );
		$item->setUrl( OW::getRouter()->urlForRoute( 'spvideolite.admin' ) );
		$item->setKey( 'tweaks' );
		$item->setIconClass( 'ow_ic_star' );
		$item->setOrder( 0 );
		$menuItems[] = $item;

		$item = new BASE_MenuItem();
		$item->setLabel( $language->text( 'spvideolite', 'adm_menu_help' ) );
		$item->setUrl( OW::getRouter()->urlForRoute( 'spvideolite.admin_help' ) );
		$item->setKey( 'help' );
		$item->setIconClass( 'ow_ic_help' );
		$item->setOrder( 1 );
		$menuItems[] = $item;

		$menu->setMenuItems( $menuItems );
		$menu->deactivateElements();

		return $menu;
	}

	function quota() {
		// $userService = BOL_UserService::getInstance();

        $roleService = BOL_AuthorizationService::getInstance();

        $roles = $roleService->findNonGuestRoleList();

        $total = 0;

        foreach ( $roles as $role )
        {
            $userCount = $roleService->countUserByRoleId($role->getId());

            $list[$role->getId()] = array(
                'dto' => $role,
                'userCount' => $userCount,
            );

            $total += $userCount;
        }

        $this->assign('set', $list);

        // $this->assign('roles',$roles);

        $this->setPageHeading( 'Quota' );
	}

	function processor() {
		$this->setPageHeading( 'Processor' );
	}

	function tweaks() {
		
	}

	function help() {
		$this->setPageHeading( 'Help and Support' );
	}

	public function categories() {
    $this->setPageHeading( 'Categories' );    
  }

  public function saveconfig( array $params) {
  	SPVIDEOLITE_BOL_Configs::getInstance()->set($_POST['key'],$_POST['value']);
  	die();
  }

}

