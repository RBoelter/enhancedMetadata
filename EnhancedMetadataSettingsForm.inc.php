<?php

import('lib.pkp.classes.form.Form');
import('lib.pkp.classes.file.FileManager');
import('classes.notification.NotificationManager');

class EnhancedMetadataSettingsForm extends Form
{
	public $plugin;
	private $folderPath;

	public function __construct($plugin)
	{
		parent::__construct($plugin->getTemplateResource('settings.tpl'));
		$contextId = Application::getRequest()->getContext()->getId();
		$this->plugin = $plugin;
		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorCSRF($this));
		$this->folderPath = Config::getVar('files', 'public_files_dir') . '/journals/' . $contextId . '/enhancedForms';
	}

	public function initData()
	{
		$contextId = Application::getRequest()->getContext()->getId();
		$data = $this->plugin->getSetting($contextId, 'settings');
		$fileManager = new FileManager();
		/* TODO READ FILE */
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
		$notificationMgr = new NotificationManager();
		$fileManager = new FileManager();

		$jsonString = $this->getData('emFile');
		$jsonObj = json_decode($jsonString, true);
		if (isset($jsonObj)) {
			if (!$fileManager->fileExists($this->folderPath))
				$fileManager->mkdir($this->folderPath);
			$filepath = $this->folderPath . '/enh_' . $jsonObj['form'] . '_' . $jsonObj['version'] . '.json';
			if ($fileManager->fileExists($filepath)) {
				$notificationMgr->createTrivialNotification(
					Application::getRequest()->getUser()->getId(),
					NOTIFICATION_TYPE_ERROR,
					/* TODO correct error msg */
					['contents' => __('common.error')]
				);
			} else {
				/* TODO SAVE FILENAMESETTINGS TO DB*/
				$fileManager->writeFile($filepath, $jsonString);
				$this->plugin->updateSetting($contextId, 'settings', json_encode($data));
				$notificationMgr->createTrivialNotification(
					Application::getRequest()->getUser()->getId(),
					NOTIFICATION_TYPE_SUCCESS,
					['contents' => __('common.changesSaved')]
				);
			}
		}
		return parent::execute();
	}
}