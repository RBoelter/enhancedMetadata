<?php
import('lib.pkp.classes.plugins.GenericPlugin');
import('lib.pkp.classes.form.validation.FormValidatorLength');

/**
 * Class EnhancedMetadataPlugin
 *
 * To make this work in 3.1.2.x add parent::execute(); in execute function(L137) of \lib\pkp\controllers\wizard\fileUpload\form\SubmissionFilesMetadataForm.inc.php
 */
class EnhancedMetadataPlugin extends GenericPlugin
{


	/**
	 * @return string plugin name
	 */
	public function getDisplayName()
	{
		return __('plugins.generic.enhancedMetadata.title');
	}

	/**
	 * @return string plugin description
	 */
	public function getDescription()
	{
		return __('plugins.generic.enhancedMetadata.desc');
	}


	public function register($category, $path, $mainContextId = NULL)
	{
		$success = parent::register($category, $path);
		if ($success && $this->getEnabled()) {
			// Add metadata fields to submission
			HookRegistry::register('Templates::Submission::SubmissionMetadataForm::AdditionalMetadata', array($this, 'metadataFormDisplay'));
			HookRegistry::register('supplementaryfilemetadataform::display', array($this, 'metadataFormDisplay'));
			// Hook for initData
			HookRegistry::register('submissionsubmitstep3form::initdata', array($this, 'submissionMetadataInitData'));
			HookRegistry::register('issueentrysubmissionreviewform::initdata', array($this, 'submissionMetadataInitData'));
			HookRegistry::register('quicksubmitform::initdata', array($this, 'submissionMetadataInitData'));
			/*HookRegistry::register('supplementaryfilemetadataform::initdata', array($this, 'submissionMetadataInitData'));*/
			// Hook for readUserVars
			HookRegistry::register('submissionsubmitstep3form::readuservars', array($this, 'addUserVars'));
			HookRegistry::register('issueentrysubmissionreviewform::readuservars', array($this, 'addUserVars'));
			HookRegistry::register('quicksubmitform::readuservars', array($this, 'addUserVars'));
			HookRegistry::register('supplementaryfilemetadataform::readuservars', array($this, 'addUserVars'));
			// Hook for save into forms
			HookRegistry::register('submissionsubmitstep3form::validate', array($this, 'submissionMetadataValidate'));

			HookRegistry::register('submissionsubmitstep3form::execute', array($this, 'submissionMetadataExecute'));
			HookRegistry::register('issueentrysubmissionreviewform::execute', array($this, 'submissionMetadataExecute'));
			HookRegistry::register('quicksubmitform::execute', array($this, 'submissionMetadataExecute'));
			HookRegistry::register('supplementaryfilemetadataform::execute', array($this, 'submissionMetadataExecute'));
			// Hook for save into db
			HookRegistry::register('articledao::getAdditionalFieldNames', array($this, 'addAdditionalFieldNames'));
			HookRegistry::register('supplementaryfiledaodelegate::getLocaleFieldNames', array($this, 'addLocaleFieldNames'));
		}
		return $success;
	}

	/**
	 * @param $hookName
	 * @param $params
	 * @return bool
	 * @throws SmartyException
	 */
	function metadataFormDisplay($hookName, $params)
	{
		$form =& $params[0];
		if (!is_array($form) && get_class($form) == 'SupplementaryFileMetadataForm') {
			$request = PKPApplication::getRequest();
			$templateMgr = TemplateManager::getManager($request);
			$submissionFile = $form->getSubmissionFile();
			if ($submissionFile)
				$form->setData('enhTest2', $submissionFile->getData('enhTest2'));
			$templateMgr->registerFilter("output", array($this, 'supplementaryMetadataFilter'));
		} else {
			$smarty =& $params[1];
			$output =& $params[2];
			$output .= $smarty->fetch($this->getTemplateResource('submissionMetaData.tpl'));
		}
		return false;
	}

	/**
	 * @param $hookName
	 * @param $params
	 * @return bool
	 */
	function submissionMetadataInitData($hookName, $params)
	{
		$form =& $params[0];
		$article = null;
		if ($form)
			switch (get_class($form)) {
				case 'QuickSubmitForm':
				case 'SubmissionSubmitStep3Form':
					$article = $form->submission;
					break;
				case 'IssueEntrySubmissionReviewForm':
					$article = $form->getSubmission();
					break;
			}
		if ($article) {
			$jsonSchema = $this->getJsonScheme($this->getPluginPath() . '/submission.json');
			if ($jsonSchema && $jsonSchema['items']) {
				$version = $jsonSchema['version'];
				if ($version && intval($version) && $version > 0) {
					$json = null;
					do {
						$json = $article->getData('enhMetaDataJson_' . $version--);
					} while ($json == null && $version > 0);
					if ($json) {
						$form->setData('enhFormFields', $jsonSchema['items']);
						$form->setData('enhMetaDataJson', json_decode($json, true));
					}
				}
			}
		}
		return false;
	}

	/**
	 * @param $hookName
	 * @param $params
	 * @return bool
	 */
	function addUserVars($hookName, $params)
	{
		$form =& $params[0];
		$userVars =& $params[1];
		if ($form)
			switch (get_class($form)) {
				case 'QuickSubmitForm':
				case 'SubmissionSubmitStep3Form':
				case 'IssueEntrySubmissionReviewForm':
					$jsonSchema = $this->getJsonScheme($this->getPluginPath() . '/submission.json');
					if ($jsonSchema && $jsonSchema['items']) {
						$names = $this->getNameParam($jsonSchema['items']);
						$this->addChecks($form, $jsonSchema['items']);
						foreach ($names as $name)
							$userVars[] = $name;
					}
					break;
				case 'SupplementaryFileMetadataForm':
					$userVars[] = 'enhTest2';
					break;
			}
		return false;
	}

	function submissionMetadataValidate($hookName, $params)
	{
		$form =& $params[0];
		$jsonSchema = $this->getJsonScheme($this->getPluginPath() . '/submission.json');
		if ($jsonSchema && $jsonSchema['items']) {
			$names = $this->getNameParam($jsonSchema['items']);
			$enhData = [];
			foreach ($names as $name) {
				$enhData[$name] = $form->getData($name);
			}
			$form->setData('enhMetaDataJson', $enhData);
			$form->setData('enhFormFields', $jsonSchema['items']);
		}
	}

	/**
	 * @param $hookName
	 * @param $params
	 * @return bool
	 */
	function submissionMetadataExecute($hookName, $params)
	{
		$form =& $params[0];
		$article = null;
		$submissionFile = null;
		switch (get_class($form)) {
			case 'SubmissionSubmitStep3Form':
			case 'QuickSubmitForm':
				$article = $form->submission;
				break;
			case 'IssueEntrySubmissionReviewForm':
				$article = $form->getSubmission();
				break;
			case 'SupplementaryFileMetadataForm':
				$submissionFile = $form->getSubmissionFile();
				break;
		}
		if ($article != null) {
			$enhData = [];
			$jsonSchema = $this->getJsonScheme($this->getPluginPath() . '/submission.json');
			if ($jsonSchema && $jsonSchema['items']) {
				$names = $this->getNameParam($jsonSchema['items']);
				foreach ($names as $name) {
					$enhData[$name] = $form->getData($name);
				}
			}
			$article->setData('enhMetaDataJson_' . $jsonSchema['version'], json_encode($enhData));
		}
		if ($submissionFile != null) {
			$submissionFile->setData('enhTest2', $form->getData('enhTest2'));
		}
		return false;
	}


	/**
	 * @param $hookName
	 * @param $params
	 * @return bool
	 */
	function addAdditionalFieldNames($hookName, $params)
	{
		$form =& $params[0];
		$jsonSchema = $this->getJsonScheme($this->getPluginPath() . '/submission.json');
		switch (get_class($form)) {
			case 'ArticleDAO':
			case 'SupplementaryFileDAODelegate':
				$fields =& $params[1];
				$fields[] = 'enhMetaDataJson_' . $jsonSchema['version'];
				break;
		}
		return false;
	}

	function supplementaryMetadataFilter($output, $templateMgr)
	{
		if (preg_match('/<fieldset\s*id="\s*fileMetaData\s*"\s*>/', $output, $matches, PREG_OFFSET_CAPTURE)) {
			$match = $matches[0][0];
			$offset = $matches[0][1];
			$newOutput = substr($output, 0, $offset + strlen($match));
			$newOutput .= $templateMgr->fetch($this->getTemplateResource('supplementaryMetaData.tpl'));
			$newOutput .= substr($output, $offset + strlen($match));
			$output = $newOutput;
			$templateMgr->unregisterFilter('output', array($this, 'supplementaryMetadataFilter'));
		}
		return $output;
	}

	function getJsonScheme($name)
	{
		return json_decode(file_get_contents($name), true);
	}

	function addChecks($form, $data)
	{
		foreach ($data as $itm) {
			if ($itm['fields'])
				foreach ($itm['fields'] as $field)
					switch ($itm['type']) {
						case 'text':
						case 'textarea';
							$form->addCheck(new FormValidatorLength($form, $field['name'] . '[en_US]', 'optional', 'user.register.form.passwordLengthRestriction', '<=', $field['maxLength']));
							break;
					}

		}
	}

	function getNameParam($data)
	{
		$res = [];
		foreach ($data as $itm) {
			switch ($itm['type']) {
				case 'select':
				case 'radio':
					$res[] = $itm['name'];
					break;
				default:
					if ($itm['fields'])
						foreach ($itm['fields'] as $field)
							$res[] = $field['name'];
			}
		}
		return array_unique($res);
	}

}
