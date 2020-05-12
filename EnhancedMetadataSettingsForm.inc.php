<?php

import('lib.pkp.classes.form.Form');


class EnhancedMetadataSettingsForm extends Form
{
	public $plugin;

	public function __construct($plugin)
	{
		parent::__construct($plugin->getTemplateResource('settings.tpl'));
		$this->plugin = $plugin;
		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorCSRF($this));
	}

	public function initData()
	{
		$contextId = Application::getRequest()->getContext()->getId();
		$data = $this->plugin->getSetting($contextId, 'settings');
		if ($data != null && $data != '') {
			$data = json_decode($data, true);
			$this->setData('emTitle', $data['title']);
			$this->setData('emFile', $data['file']);
		}
		parent::initData();
	}

	public function readInputData()
	{
		$this->readUserVars(['emTitle', 'emFile']);
		parent::readInputData();
	}

	public function fetch($request, $template = null, $display = false)
	{
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('pluginName', $this->plugin->getName());
		return parent::fetch($request, $template, $display);
	}

	public function execute(...$args)
	{
		$contextId = Application::getRequest()->getContext()->getId();
		$data = [
			"title" => $this->getData('emTitle'),
			"file" => $this->getData('emFile')
		];
		import('lib.pkp.classes.file.FileManager');
		$fileManager = new FileManager();

		$this->plugin->updateSetting($contextId, 'settings', json_encode($data));
		import('classes.notification.NotificationManager');
		$notificationMgr = new NotificationManager();
		$notificationMgr->createTrivialNotification(
			Application::getRequest()->getUser()->getId(),
			NOTIFICATION_TYPE_SUCCESS,
			['contents' => __('common.changesSaved')]
		);
		return parent::execute();
	}
}