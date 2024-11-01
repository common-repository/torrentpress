<?php
class torrentpress{

	var $validPages = array('dashboard', 'manage', 'edit');
	var $page = '';

	function torrentpress(){
		add_action('admin_menu',array(&$this, 'admin_init'));
	}

	function admin_init(){
		$this->add_admin_pages();
	}
	
	function add_admin_pages(){
		add_menu_page('TorrentPress', 'TorrentPress', 'administrator', 'torrentpress', array(&$this, 'admin_page'));
		add_submenu_page('torrentpress', 'Manage Torrents', 'Manage Torrents', 'administrator', 'torrentpress/manage', array(&$this, 'admin_page'));
		add_submenu_page('torrentpress', 'Edit Torrent', 'Edit Torrent', 'administrator', 'torrentpress/edit', array(&$this, 'admin_page'));
	}
	
	function admin_page(){
		$defaultpage = 'dashboard';
		
		$area = explode('/',$_GET['page']);
		$this->page = isset($area[1]) ? $area[1] : $defaultpage;
	
		if( ! in_array($this->page, $this->validPages) )
			$this->page = $defaultpage;
		
		do_action('tp_admin-' . $this->page, $this->page);
	}
	
	
}
?>